<?php

namespace App\Services;

use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class SalesReturnService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AccountingJournalService $accountingJournalService,
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function store(SalesTransaction $salesTransaction, array $attributes, array $items, string $intent = 'submit'): SalesReturn
    {
        $salesTransaction->loadMissing(['outlet', 'items']);

        if (! $salesTransaction->outlet || ! $salesTransaction->outlet->warehouse_id) {
            throw new DomainException('Retur gagal diproses karena outlet transaksi belum terhubung ke gudang.');
        }

        $returnDate = isset($attributes['return_date'])
            ? Carbon::parse($attributes['return_date'])->toDateString()
            : now('Asia/Jakarta')->toDateString();

        return DB::transaction(function () use ($salesTransaction, $attributes, $items, $intent, $returnDate): SalesReturn {
            $return = SalesReturn::query()->create([
                'tenant_id' => $salesTransaction->tenant_id,
                'location_id' => $salesTransaction->location_id,
                'sales_transaction_id' => $salesTransaction->id,
                'return_number' => $this->generateNumber(),
                'return_date' => $returnDate,
                'status' => SalesReturn::STATUS_DRAFT,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->syncItems($return, $salesTransaction, $items);
            $this->applyIntent($return, $intent);
            $return->save();

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('sales', 'sales_return.store', 'Draft retur penjualan dibuat', $return, [
                'status' => $return->status,
                'transaction_id' => $salesTransaction->id,
                'refund_amount' => (float) $return->refund_amount,
            ]);

            return $return->fresh(['salesTransaction', 'items.product', 'items.productVariant']);
        });
    }

    public function submit(SalesReturn $salesReturn): SalesReturn
    {
        if (! in_array($salesReturn->status, [SalesReturn::STATUS_DRAFT, SalesReturn::STATUS_REJECTED], true)) {
            throw new DomainException('Hanya draft atau retur yang ditolak yang bisa disubmit ulang.');
        }

        $salesReturn->status = SalesReturn::STATUS_PENDING_APPROVAL;
        $salesReturn->submitted_at = Carbon::now();
        $salesReturn->approved_at = null;
        $salesReturn->approved_by = null;
        $salesReturn->save();

        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('sales', 'sales_return.submit', 'Retur penjualan dikirim ke approval', $salesReturn, [
            'status' => $salesReturn->status,
        ]);

        return $salesReturn;
    }

    public function approve(SalesReturn $salesReturn): SalesReturn
    {
        if (! $salesReturn->canBeApproved()) {
            throw new DomainException('Retur penjualan ini tidak berada pada status pending approval.');
        }

        $salesReturn->loadMissing(['salesTransaction.outlet', 'items.product']);
        $salesTransaction = $salesReturn->salesTransaction;

        if (! $salesTransaction || ! $salesTransaction->outlet || ! $salesTransaction->outlet->warehouse_id) {
            throw new DomainException('Approval retur gagal karena outlet transaksi belum terhubung ke gudang.');
        }

        $this->periodLockService->assertDateIsOpen($salesReturn->return_date, 'Approval retur penjualan');

        return DB::transaction(function () use ($salesReturn, $salesTransaction): SalesReturn {
            $warehouseId = (int) $salesTransaction->outlet->warehouse_id;
            $refundAmount = 0.0;
            $costOfGoodsReturned = 0.0;

            foreach ($salesReturn->items as $item) {
                $quantity = (float) $item->quantity;
                if ($quantity <= 0) {
                    continue;
                }

                $this->stockService->post(
                    productId: (int) $item->product_id,
                    warehouseId: $warehouseId,
                    movementType: 'return_from_customer',
                    referenceType: 'sales_return',
                    referenceId: (int) $salesReturn->id,
                    quantity: $quantity,
                    unitCost: (float) $item->unit_cost,
                    notes: 'Retur penjualan ' . $salesReturn->return_number,
                    transactionAt: Carbon::parse($salesReturn->return_date)->endOfDay(),
                    transferStatus: null,
                    productVariantId: $item->product_variant_id ? (int) $item->product_variant_id : null,
                );

                $refundAmount += (float) $item->line_total;
                $costOfGoodsReturned += $quantity * (float) $item->unit_cost;
            }

            if ($refundAmount <= 0) {
                throw new DomainException('Retur penjualan tidak memiliki nominal refund yang valid.');
            }

            $salesReturn->refund_amount = $refundAmount;
            $salesReturn->status = SalesReturn::STATUS_APPROVED;
            $salesReturn->approved_at = Carbon::now();
            $salesReturn->approved_by = $this->actorId();
            $salesReturn->save();

            $salesTransaction->refunded_amount = (float) $salesTransaction->refunded_amount + $refundAmount;
            $salesTransaction->last_refunded_at = Carbon::now();
            if ((float) $salesTransaction->refunded_amount + 0.01 >= (float) $salesTransaction->net_amount) {
                $salesTransaction->status = 'refunded';
            }
            $salesTransaction->save();

            $this->accountingJournalService->postPosRefund(
                salesReturn: $salesReturn,
                refundAmount: $refundAmount,
                costOfGoodsReturned: $costOfGoodsReturned,
                entryDate: Carbon::parse($salesReturn->return_date)->endOfDay(),
            );

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('sales', 'sales_return.approve', 'Retur penjualan disetujui', $salesReturn, [
                'status' => $salesReturn->status,
                'refund_amount' => $refundAmount,
                'cost_of_goods_returned' => $costOfGoodsReturned,
                'transaction_id' => $salesReturn->sales_transaction_id,
            ]);

            return $salesReturn->fresh(['salesTransaction', 'items.product', 'items.productVariant']);
        });
    }

    public function reject(SalesReturn $salesReturn, ?string $reason = null): SalesReturn
    {
        if (! $salesReturn->canBeApproved()) {
            throw new DomainException('Hanya retur penjualan pending approval yang bisa ditolak.');
        }

        $salesReturn->status = SalesReturn::STATUS_REJECTED;
        $salesReturn->approved_at = null;
        $salesReturn->approved_by = null;
        $salesReturn->notes = $this->appendReason($salesReturn->notes, 'Rejected', $reason);
        $salesReturn->save();

        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('sales', 'sales_return.reject', 'Retur penjualan ditolak', $salesReturn, [
            'status' => $salesReturn->status,
            'reason' => $reason,
        ]);

        return $salesReturn;
    }

    private function syncItems(SalesReturn $salesReturn, SalesTransaction $salesTransaction, array $items): void
    {
        $normalizedItems = collect($items)
            ->map(function (array $item) use ($salesTransaction): array {
                $line = $this->resolveSourceLine($salesTransaction, $item);
                $quantity = (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new DomainException('Qty retur harus lebih besar dari 0.');
                }

                $maxReturnable = $this->maxReturnableQuantity($line);
                if ($quantity - $maxReturnable > 0.0001) {
                    throw new DomainException('Qty retur melebihi quantity transaksi yang bisa diretur.');
                }

                $unitPrice = (float) ($item['unit_price'] ?? $line->unit_price);
                $unitCost = (float) $line->unit_cost;

                return [
                    'sales_transaction_item_id' => $line->id,
                    'product_id' => $line->product_id,
                    'product_variant_id' => $line->product_variant_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'line_total' => max($quantity * $unitPrice, 0),
                    'reason' => $item['reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            })
            ->values();

        if ($normalizedItems->isEmpty()) {
            throw new DomainException('Retur penjualan harus memiliki minimal satu item.');
        }

        $salesReturn->items()->delete();
        $salesReturn->items()->createMany($normalizedItems->all());
        $salesReturn->load('items');
        $salesReturn->recalculateTotals($salesReturn->items);
    }

    private function resolveSourceLine(SalesTransaction $salesTransaction, array $item): SalesTransactionItem
    {
        if (isset($item['sales_transaction_item_id'])) {
            return $salesTransaction->items
                ->firstWhere('id', (int) $item['sales_transaction_item_id'])
                ?? throw new DomainException('Line transaksi sumber tidak ditemukan untuk retur.');
        }

        if (isset($item['product_id']) && isset($item['product_variant_id'])) {
            /** @var SalesTransactionItem|null $line */
            $line = $salesTransaction->items
                ->where('product_id', (int) $item['product_id'])
                ->where('product_variant_id', (int) $item['product_variant_id'])
                ->first(fn (SalesTransactionItem $candidate): bool => $this->maxReturnableQuantity($candidate) > 0);

            if ($line !== null) {
                return $line;
            }

            throw new DomainException('Kombinasi product_id dan product_variant_id pada item retur tidak valid.');
        }

        if (isset($item['product_variant_id'])) {
            /** @var SalesTransactionItem|null $line */
            $line = $salesTransaction->items
                ->where('product_variant_id', (int) $item['product_variant_id'])
                ->first(fn (SalesTransactionItem $candidate): bool => $this->maxReturnableQuantity($candidate) > 0);

            if ($line !== null) {
                return $line;
            }
        }

        if (isset($item['product_id'])) {
            /** @var SalesTransactionItem|null $line */
            $line = $salesTransaction->items
                ->where('product_id', (int) $item['product_id'])
                ->first(fn (SalesTransactionItem $candidate): bool => $this->maxReturnableQuantity($candidate) > 0);

            if ($line !== null) {
                return $line;
            }
        }

        throw new DomainException('Item retur harus mereferensikan produk/line transaksi yang valid.');
    }

    private function maxReturnableQuantity(SalesTransactionItem $line): float
    {
        $alreadyReturned = (float) SalesReturnItem::query()
            ->where('sales_transaction_item_id', $line->id)
            ->whereHas('salesReturn', fn ($query) => $query->where('status', SalesReturn::STATUS_APPROVED))
            ->sum('quantity');

        return max((float) $line->quantity - $alreadyReturned, 0);
    }

    private function applyIntent(SalesReturn $salesReturn, string $intent): void
    {
        if ($intent === 'submit') {
            $salesReturn->status = SalesReturn::STATUS_PENDING_APPROVAL;
            $salesReturn->submitted_at = $salesReturn->submitted_at ?? Carbon::now();
            $salesReturn->approved_at = null;
            $salesReturn->approved_by = null;

            return;
        }

        $salesReturn->status = SalesReturn::STATUS_DRAFT;
        $salesReturn->submitted_at = null;
        $salesReturn->approved_at = null;
        $salesReturn->approved_by = null;
    }

    private function generateNumber(): string
    {
        $prefix = 'SR-' . Carbon::now('Asia/Jakarta')->format('ym');
        $latest = SalesReturn::query()
            ->where('return_number', 'like', $prefix . '-%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $lastSequence = $latest ? (int) substr($latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }

    private function actorId(): int
    {
        $authId = auth()->id();
        if ($authId !== null) {
            return (int) $authId;
        }

        return (int) User::query()->firstOrCreate(
            ['email' => 'system.sales.refund@webstellar.local'],
            ['name' => 'System Sales Refund', 'password' => 'password']
        )->id;
    }

    private function appendReason(?string $existing, string $label, ?string $reason): string
    {
        $detail = trim((string) $reason);
        $line = '[' . $label . '] ' . ($detail !== '' ? $detail : 'Aksi dilakukan dari workflow retur penjualan.');

        return trim(trim((string) $existing) . PHP_EOL . $line);
    }
}
