<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesTransaction;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalesTransactionService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AccountingJournalService $accountingJournalService,
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function store(array $attributes, array $items, array $payments): SalesTransaction
    {
        DB::beginTransaction();

        try {
            $outlet = Outlet::query()->with('warehouse')->findOrFail($attributes['outlet_id']);
            $customer = isset($attributes['customer_id']) ? Customer::query()->find($attributes['customer_id']) : null;

            if (! $outlet->warehouse_id) {
                throw new DomainException('Outlet ini belum terhubung ke gudang toko, jadi transaksi POS belum bisa diposting ke stok.');
            }

            $soldAt = isset($attributes['sold_at'])
                ? CarbonImmutable::parse($attributes['sold_at'])
                : CarbonImmutable::now('Asia/Jakarta');
            $this->periodLockService->assertDateIsOpen($soldAt, 'Posting transaksi POS');

            $transaction = new SalesTransaction([
                'tenant_id' => $outlet->tenant_id,
                'location_id' => $outlet->location_id,
                'outlet_id' => $outlet->id,
                'cashier_employee_id' => $attributes['cashier_employee_id'] ?? null,
                'customer_id' => $customer?->id,
                'transaction_number' => $this->generateNumber($soldAt),
                'invoice_number' => $this->generateInvoiceNumber($soldAt),
                'sold_at' => $soldAt,
                'invoice_date' => $attributes['invoice_date'] ?? $soldAt->toDateString(),
                'status' => 'paid',
                'customer_name' => $customer?->name ?? ($attributes['customer_name'] ?? null),
                'notes' => $attributes['notes'] ?? null,
            ]);

            $grossAmount = 0.0;
            $discountAmount = 0.0;
            $netAmount = 0.0;
            $costOfGoodsSold = 0.0;
            $itemCount = 0.0;
            $normalizedItems = [];

            foreach ($items as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) ($item['unit_price'] ?? $product->selling_price);
                $unitCost = (float) $product->cost_price;
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $lineGross = $quantity * $unitPrice;
                $lineTotal = max($lineGross - $lineDiscount, 0);

                $grossAmount += $lineGross;
                $discountAmount += $lineDiscount;
                $netAmount += $lineTotal;
                $costOfGoodsSold += ($quantity * $unitCost);
                $itemCount += $quantity;

                $normalizedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'discount_amount' => $lineDiscount,
                    'line_total' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $paymentTotal = collect($payments)->sum(fn (array $payment): float => (float) $payment['amount']);

            if ($payments === []) {
                throw new DomainException('Transaksi POS wajib memiliki minimal satu metode pembayaran.');
            }

            if (abs($paymentTotal - $netAmount) > 0.01) {
                throw new DomainException('Total bayar harus sama persis dengan total belanja.');
            }

            $transaction->gross_amount = $grossAmount;
            $transaction->discount_amount = $discountAmount;
            $transaction->tax_amount = 0;
            $transaction->net_amount = $netAmount;
            $transaction->items_count = (int) round($itemCount);
            $transaction->due_date = $attributes['due_date'] ?? $transaction->invoice_date;
            $transaction->payment_status = 'paid';
            $transaction->paid_amount = $paymentTotal;
            $transaction->balance_due = 0;
            $transaction->split_payment_count = 0;
            $transaction->save();

            foreach ($normalizedItems as $line) {
                $transaction->items()->create($line);

                $this->stockService->post(
                    $line['product_id'],
                    (int) $outlet->warehouse_id,
                    'sale',
                    'sales_transaction',
                    (int) $transaction->id,
                    -1 * (float) $line['quantity'],
                    (float) $line['unit_cost'],
                    'POS ' . $transaction->transaction_number,
                    $soldAt,
                );
            }

            foreach ($payments as $payment) {
                $paymentMethod = PaymentMethod::query()->findOrFail($payment['payment_method_id']);

                $transaction->payments()->create([
                    'tenant_id' => $transaction->tenant_id,
                    'location_id' => $transaction->location_id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => (float) $payment['amount'],
                    'reference_number' => $payment['reference_number'] ?? null,
                    'approval_code' => $payment['approval_code'] ?? null,
                    'settled_at' => $soldAt->addDays((int) ($paymentMethod->settlement_days ?? 0)),
                ]);
            }

            $transaction->load('payments');
            $transaction->recalculateSettlement($transaction->payments);
            $transaction->save();

            $this->accountingJournalService->postPosSale(
                transaction: $transaction,
                cashReceived: $paymentTotal,
                salesAmount: $netAmount,
                costOfGoodsSold: $costOfGoodsSold,
                entryDate: $soldAt,
            );

            DB::commit();
            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('sales', 'pos.store', 'Transaksi POS diposting', $transaction, [
                'transaction_number' => $transaction->transaction_number,
                'net_amount' => (float) $transaction->net_amount,
                'payment_total' => $paymentTotal,
                'items_count' => $transaction->items_count,
            ]);

            return $transaction->fresh(['outlet', 'cashier', 'customer', 'items.product', 'payments.paymentMethod']);
        } catch (Throwable $exception) {
            DB::rollBack();
            $this->auditLogService->log('sales', 'pos.store_failed', 'Posting transaksi POS gagal', 'sales_transaction', [
                'error' => $exception->getMessage(),
                'outlet_id' => $attributes['outlet_id'] ?? null,
            ]);

            if ($exception instanceof DomainException) {
                throw $exception;
            }

            throw new DomainException('Transaksi POS gagal diproses secara atomik. Silakan ulangi proses checkout.', previous: $exception);
        }
    }

    private function generateNumber(CarbonImmutable $soldAt): string
    {
        $prefix = 'POS-' . $soldAt->format('Ymd');
        $latest = SalesTransaction::query()
            ->where('transaction_number', 'like', $prefix . '-%')
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $lastSequence = $latest ? (int) substr($latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }

    private function generateInvoiceNumber(CarbonImmutable $soldAt): string
    {
        $prefix = 'INV-' . $soldAt->format('Ymd');
        $latest = SalesTransaction::query()
            ->where('invoice_number', 'like', $prefix . '-%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $lastSequence = $latest ? (int) substr($latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }
}
