<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SalesTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class SalesTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = Outlet::query()->pluck('id', 'code');
        $cashiers = Employee::query()->pluck('id', 'employee_code');
        $paymentMethods = PaymentMethod::query()->pluck('id', 'code');
        $customers = Customer::query()->pluck('id', 'code');
        $products = Product::query()->orderBy('id')->get()->values();

        $transactions = [
            ['number' => 'POS-20260408-001', 'invoice' => 'INV-20260408-001', 'outlet' => 'OTL-JKT01', 'cashier' => 'EMP-002', 'sold_at' => '2026-04-08 09:11:00', 'gross' => 465000, 'discount' => 15000, 'tax' => 0, 'items' => 6, 'status' => 'paid', 'customer' => null, 'payments' => [['method' => 'QRIS', 'amount' => 250000], ['method' => 'CASH', 'amount' => 200000]]],
            ['number' => 'POS-20260408-002', 'invoice' => 'INV-20260408-002', 'outlet' => 'OTL-JKT01', 'cashier' => 'EMP-002', 'sold_at' => '2026-04-08 11:42:00', 'gross' => 289000, 'discount' => 9000, 'tax' => 0, 'items' => 4, 'status' => 'paid', 'customer' => 'CUST-001', 'due_date' => '2026-04-29', 'payments' => [['method' => 'DEBIT-BCA', 'amount' => 180000]]],
            ['number' => 'POS-20260408-003', 'invoice' => 'INV-20260408-003', 'outlet' => 'OTL-BDG01', 'cashier' => 'EMP-004', 'sold_at' => '2026-04-08 12:15:00', 'gross' => 198000, 'discount' => 8000, 'tax' => 0, 'items' => 3, 'status' => 'paid', 'customer' => 'CUST-002', 'due_date' => '2026-04-22', 'payments' => [['method' => 'GOPAY', 'amount' => 70000]]],
            ['number' => 'POS-20260408-004', 'invoice' => 'INV-20260408-004', 'outlet' => 'OTL-SBY01', 'cashier' => 'EMP-006', 'sold_at' => '2026-04-08 14:30:00', 'gross' => 512000, 'discount' => 12000, 'tax' => 0, 'items' => 7, 'status' => 'paid', 'customer' => 'CUST-003', 'due_date' => '2026-05-08', 'payments' => [['method' => 'CC-VISA', 'amount' => 500000]]],
            ['number' => 'POS-20260408-005', 'invoice' => 'INV-20260408-005', 'outlet' => 'OTL-BKS01', 'cashier' => 'EMP-007', 'sold_at' => '2026-04-08 15:05:00', 'gross' => 346000, 'discount' => 6000, 'tax' => 0, 'items' => 5, 'status' => 'paid', 'customer' => null, 'payments' => [['method' => 'CASH', 'amount' => 200000], ['method' => 'QRIS', 'amount' => 140000]]],
            ['number' => 'POS-20260408-006', 'invoice' => 'INV-20260408-006', 'outlet' => 'OTL-SBY01', 'cashier' => 'EMP-005', 'sold_at' => '2026-04-08 18:10:00', 'gross' => 624000, 'discount' => 24000, 'tax' => 0, 'items' => 8, 'status' => 'paid', 'customer' => 'CUST-004', 'due_date' => '2026-04-15', 'payments' => [['method' => 'TRANSFER', 'amount' => 300000]]],
            ['number' => 'POS-20260407-001', 'invoice' => 'INV-20260407-001', 'outlet' => 'OTL-JKT01', 'cashier' => 'EMP-002', 'sold_at' => '2026-04-07 10:05:00', 'gross' => 402000, 'discount' => 12000, 'tax' => 0, 'items' => 6, 'status' => 'paid', 'customer' => null, 'payments' => [['method' => 'QRIS', 'amount' => 390000]]],
            ['number' => 'POS-20260407-002', 'invoice' => 'INV-20260407-002', 'outlet' => 'OTL-BDG01', 'cashier' => 'EMP-004', 'sold_at' => '2026-04-07 13:25:00', 'gross' => 221000, 'discount' => 11000, 'tax' => 0, 'items' => 4, 'status' => 'paid', 'customer' => 'CUST-001', 'due_date' => '2026-04-28', 'payments' => [['method' => 'CASH', 'amount' => 210000]]],
            ['number' => 'POS-20260407-003', 'invoice' => 'INV-20260407-003', 'outlet' => 'OTL-SBY01', 'cashier' => 'EMP-006', 'sold_at' => '2026-04-07 16:40:00', 'gross' => 488000, 'discount' => 18000, 'tax' => 0, 'items' => 6, 'status' => 'paid', 'customer' => 'CUST-003', 'due_date' => '2026-05-07', 'payments' => [['method' => 'DEBIT-BCA', 'amount' => 270000], ['method' => 'GOPAY', 'amount' => 200000]]],
            ['number' => 'POS-20260407-004', 'invoice' => 'INV-20260407-004', 'outlet' => 'OTL-BKS01', 'cashier' => 'EMP-007', 'sold_at' => '2026-04-07 19:10:00', 'gross' => 305000, 'discount' => 5000, 'tax' => 0, 'items' => 5, 'status' => 'paid', 'customer' => null, 'payments' => [['method' => 'CASH', 'amount' => 300000]]],
        ];

        foreach ($transactions as $index => $transactionData) {
            $netAmount = $transactionData['gross'] - $transactionData['discount'] + $transactionData['tax'];
            $paymentTotal = collect($transactionData['payments'])->sum('amount');
            $paidAmount = min($paymentTotal, $netAmount);
            $balanceDue = max($netAmount - $paidAmount, 0);

            $transaction = SalesTransaction::query()->updateOrCreate(
                ['transaction_number' => $transactionData['number']],
                [
                    'outlet_id' => $outlets[$transactionData['outlet']] ?? null,
                    'cashier_employee_id' => $cashiers[$transactionData['cashier']] ?? null,
                    'customer_id' => $transactionData['customer'] ? ($customers[$transactionData['customer']] ?? null) : null,
                    'invoice_number' => $transactionData['invoice'],
                    'sold_at' => CarbonImmutable::parse($transactionData['sold_at']),
                    'invoice_date' => CarbonImmutable::parse($transactionData['sold_at'])->toDateString(),
                    'due_date' => $transactionData['due_date'] ?? CarbonImmutable::parse($transactionData['sold_at'])->toDateString(),
                    'gross_amount' => $transactionData['gross'],
                    'discount_amount' => $transactionData['discount'],
                    'tax_amount' => $transactionData['tax'],
                    'net_amount' => $netAmount,
                    'paid_amount' => $paidAmount,
                    'balance_due' => $balanceDue,
                    'payment_status' => match (true) {
                        $balanceDue <= 0.0001 => 'paid',
                        $paidAmount > 0 => 'partial',
                        default => 'unpaid',
                    },
                    'split_payment_count' => count($transactionData['payments']),
                    'items_count' => $transactionData['items'],
                    'status' => $transactionData['status'],
                    'customer_name' => $transactionData['customer'] ? Customer::query()->find($customers[$transactionData['customer']] ?? null)?->name : null,
                    'notes' => 'Seed sales transaction',
                ]
            );

            $transaction->payments()->delete();
            $transaction->items()->delete();

            foreach ($transactionData['payments'] as $payment) {
                $transaction->payments()->create([
                    'tenant_id' => $transaction->tenant_id,
                    'location_id' => $transaction->location_id,
                    'payment_method_id' => $paymentMethods[$payment['method']] ?? null,
                    'amount' => $payment['amount'],
                    'reference_number' => 'REF-' . substr($transactionData['number'], -3) . '-' . $payment['method'],
                    'approval_code' => $payment['method'] === 'CASH' ? null : 'APP-' . substr(md5($transactionData['number'] . $payment['method']), 0, 6),
                    'settled_at' => CarbonImmutable::parse($transactionData['sold_at'])->addDays(in_array($payment['method'], ['CC-VISA', 'DEBIT-BCA'], true) ? 2 : 0),
                ]);
            }

            $this->seedItems($transaction, $products, $transactionData['gross'], $transactionData['discount'], $index);
        }
    }

    private function seedItems(SalesTransaction $transaction, $products, float $grossAmount, float $discountAmount, int $offset): void
    {
        if ($products->count() < 2) {
            return;
        }

        $firstProduct = $products[$offset % $products->count()];
        $secondProduct = $products[($offset + 1) % $products->count()];
        $firstQuantity = max((int) floor($transaction->items_count / 2), 1);
        $secondQuantity = max((int) $transaction->items_count - $firstQuantity, 1);
        $firstGross = round($grossAmount * 0.55, 2);
        $secondGross = $grossAmount - $firstGross;

        $items = [
            [
                'product_id' => $firstProduct->id,
                'quantity' => $firstQuantity,
                'unit_price' => round($firstGross / $firstQuantity, 2),
                'unit_cost' => (float) $firstProduct->cost_price,
                'discount_amount' => $discountAmount,
                'line_total' => max($firstGross - $discountAmount, 0),
                'notes' => 'Seed line item A',
            ],
            [
                'product_id' => $secondProduct->id,
                'quantity' => $secondQuantity,
                'unit_price' => round($secondGross / $secondQuantity, 2),
                'unit_cost' => (float) $secondProduct->cost_price,
                'discount_amount' => 0,
                'line_total' => $secondGross,
                'notes' => 'Seed line item B',
            ],
        ];

        foreach ($items as $item) {
            $transaction->items()->create($item);
        }
    }
}
