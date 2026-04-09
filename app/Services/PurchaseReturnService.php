<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AccountingJournalService $accountingJournalService,
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'submit'): PurchaseReturn
    {
        return DB::transaction(function () use ($attributes, $items, $intent): PurchaseReturn {
            $purchaseOrder = isset($attributes['purchase_order_id'])
                ? PurchaseOrder::query()->with('items')->findOrFail($attributes['purchase_order_id'])
                : null;

            $supplier = Supplier::query()->findOrFail($attributes['supplier_id'] ?? $purchaseOrder?->supplier_id);
            $warehouse = Warehouse::query()->findOrFail($attributes['warehouse_id'] ?? $purchaseOrder?->warehouse_id);
            $returnDate = isset($attributes['return_date'])
                ? Carbon::parse($attributes['return_date'])->toDateString()
                : now('Asia/Jakarta')->toDateString();

            $purchaseReturn = PurchaseReturn::query()->create([
                'tenant_id' => $warehouse->tenant_id,
                'location_id' => $warehouse->location_id,
                'purchase_order_id' => $purchaseOrder?->id,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'return_number' => $this->generateNumber(),
                'return_date' => $returnDate,
                'status' => PurchaseReturn::STATUS_DRAFT,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->syncItems($purchaseReturn, $purchaseOrder, $items);
            $this->applyIntent($purchaseReturn, $intent);
            $purchaseReturn->save();

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('procurement', 'purchase_return.store', 'Draft retur pembelian dibuat', $purchaseReturn, [
                'status' => $purchaseReturn->status,
                'supplier_id' => $purchaseReturn->supplier_id,
                'warehouse_id' => $purchaseReturn->warehouse_id,
                'total_amount' => (float) $purchaseReturn->total_amount,
            ]);

            return $purchaseReturn->fresh(['supplier', 'warehouse', 'purchaseOrder', 'items.product', 'items.productVariant']);
        });
    }

    public function submit(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        if (! in_array($purchaseReturn->status, [PurchaseReturn::STATUS_DRAFT, PurchaseReturn::STATUS_REJECTED], true)) {
            throw new DomainException('Hanya draft atau retur pembelian yang ditolak yang bisa disubmit ulang.');
        }

        $purchaseReturn->status = PurchaseReturn::STATUS_PENDING_APPROVAL;
        $purchaseReturn->submitted_at = Carbon::now();
        $purchaseReturn->approved_at = null;
        $purchaseReturn->approved_by = null;
        $purchaseReturn->save();

        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('procurement', 'purchase_return.submit', 'Retur pembelian dikirim ke approval', $purchaseReturn, [
            'status' => $purchaseReturn->status,
        ]);

        return $purchaseReturn;
    }

    public function approve(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        if (! $purchaseReturn->canBeApproved()) {
            throw new DomainException('Retur pembelian ini tidak berada pada status pending approval.');
        }

        $this->periodLockService->assertDateIsOpen($purchaseReturn->return_date, 'Approval retur pembelian');
        $purchaseReturn->loadMissing(['purchaseOrder.items', 'items.product']);

        return DB::transaction(function () use ($purchaseReturn): PurchaseReturn {
            $totalAmount = 0.0;

            foreach ($purchaseReturn->items as $item) {
                $quantity = (float) $item->quantity;
                if ($quantity <= 0) {
                    continue;
                }

                if ($item->purchase_order_item_id !== null) {
                    $line = PurchaseOrderItem::query()->find($item->purchase_order_item_id);
                    if ($line !== null) {
                        $maxReturnable = $this->maxReturnableQuantity($line);
                        if ($quantity - $maxReturnable > 0.0001) {
                            throw new DomainException('Qty retur pembelian melebihi qty yang sudah diterima.');
                        }
                    }
                }

                $this->stockService->post(
                    productId: (int) $item->product_id,
                    warehouseId: (int) $purchaseReturn->warehouse_id,
                    movementType: 'return_to_supplier',
                    referenceType: 'purchase_return',
                    referenceId: (int) $purchaseReturn->id,
                    quantity: -1 * $quantity,
                    unitCost: (float) $item->unit_cost,
                    notes: 'Retur pembelian ' . $purchaseReturn->return_number,
                    transactionAt: Carbon::parse($purchaseReturn->return_date)->endOfDay(),
                    transferStatus: null,
                    productVariantId: $item->product_variant_id ? (int) $item->product_variant_id : null,
                );

                $totalAmount += (float) $item->line_total;
            }

            if ($totalAmount <= 0) {
                throw new DomainException('Retur pembelian tidak memiliki nominal yang valid.');
            }

            $purchaseReturn->total_amount = $totalAmount;
            $purchaseReturn->status = PurchaseReturn::STATUS_APPROVED;
            $purchaseReturn->approved_at = Carbon::now();
            $purchaseReturn->approved_by = $this->actorId();
            $purchaseReturn->save();

            $this->accountingJournalService->postPurchaseReturn(
                purchaseReturn: $purchaseReturn,
                returnAmount: $totalAmount,
                entryDate: Carbon::parse($purchaseReturn->return_date)->endOfDay(),
            );

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('procurement', 'purchase_return.approve', 'Retur pembelian disetujui', $purchaseReturn, [
                'status' => $purchaseReturn->status,
                'total_amount' => $totalAmount,
            ]);

            return $purchaseReturn->fresh(['supplier', 'warehouse', 'purchaseOrder', 'items.product', 'items.productVariant']);
        });
    }

    public function reject(PurchaseReturn $purchaseReturn, ?string $reason = null): PurchaseReturn
    {
        if (! $purchaseReturn->canBeApproved()) {
            throw new DomainException('Hanya retur pembelian pending approval yang bisa ditolak.');
        }

        $purchaseReturn->status = PurchaseReturn::STATUS_REJECTED;
        $purchaseReturn->approved_at = null;
        $purchaseReturn->approved_by = null;
        $purchaseReturn->notes = $this->appendReason($purchaseReturn->notes, 'Rejected', $reason);
        $purchaseReturn->save();

        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('procurement', 'purchase_return.reject', 'Retur pembelian ditolak', $purchaseReturn, [
            'status' => $purchaseReturn->status,
            'reason' => $reason,
        ]);

        return $purchaseReturn;
    }

    private function syncItems(PurchaseReturn $purchaseReturn, ?PurchaseOrder $purchaseOrder, array $items): void
    {
        $normalizedItems = collect($items)
            ->map(function (array $item) use ($purchaseOrder): array {
                $quantity = (float) ($item['quantity'] ?? 0);
                if ($quantity <= 0) {
                    throw new DomainException('Qty retur pembelian harus lebih besar dari 0.');
                }

                $purchaseOrderItem = null;
                $product = null;
                $variant = null;
                $unitCost = (float) ($item['unit_cost'] ?? 0);

                if (isset($item['purchase_order_item_id'])) {
                    $purchaseOrderItem = PurchaseOrderItem::query()->findOrFail($item['purchase_order_item_id']);
                    $product = $purchaseOrderItem->product;
                    if ($purchaseOrderItem->product_variant_id !== null) {
                        $variant = ProductVariant::query()->find($purchaseOrderItem->product_variant_id);
                    }
                    $unitCost = (float) ($item['unit_cost'] ?? $purchaseOrderItem->unit_cost);
                } elseif (isset($item['product_variant_id'])) {
                    $variant = ProductVariant::query()->with('product')->findOrFail((int) $item['product_variant_id']);
                    $product = $variant->product;
                    if (isset($item['product_id']) && $product !== null && (int) $item['product_id'] !== (int) $product->id) {
                        throw new DomainException('Kombinasi product_id dan product_variant_id pada retur pembelian tidak valid.');
                    }
                } elseif (isset($item['product_id'])) {
                    $product = Product::query()->findOrFail((int) $item['product_id']);
                    $variant = $this->stockService->resolveProductVariant((int) $product->id);
                }

                if ($purchaseOrder !== null && $purchaseOrderItem !== null && $purchaseOrderItem->purchase_order_id !== $purchaseOrder->id) {
                    throw new DomainException('Item retur tidak sesuai dengan purchase order sumber.');
                }

                if ($purchaseOrderItem !== null) {
                    $maxReturnable = $this->maxReturnableQuantity($purchaseOrderItem);
                    if ($quantity - $maxReturnable > 0.0001) {
                        throw new DomainException('Qty retur pembelian melebihi qty yang diterima.');
                    }
                }

                if ($product === null) {
                    throw new DomainException('Item retur pembelian harus mereferensikan produk yang valid.');
                }

                return [
                    'purchase_order_item_id' => $purchaseOrderItem?->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => max($quantity * $unitCost, 0),
                    'reason' => $item['reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            })
            ->values();

        if ($normalizedItems->isEmpty()) {
            throw new DomainException('Retur pembelian harus memiliki minimal satu item.');
        }

        $purchaseReturn->items()->delete();
        $purchaseReturn->items()->createMany($normalizedItems->all());
        $purchaseReturn->load('items');
        $purchaseReturn->recalculateTotals($purchaseReturn->items);
    }

    private function maxReturnableQuantity(PurchaseOrderItem $line): float
    {
        $alreadyReturned = (float) PurchaseReturnItem::query()
            ->where('purchase_order_item_id', $line->id)
            ->whereHas('purchaseReturn', fn ($query) => $query->where('status', PurchaseReturn::STATUS_APPROVED))
            ->sum('quantity');

        return max((float) $line->received_quantity - $alreadyReturned, 0);
    }

    private function applyIntent(PurchaseReturn $purchaseReturn, string $intent): void
    {
        if ($intent === 'submit') {
            $purchaseReturn->status = PurchaseReturn::STATUS_PENDING_APPROVAL;
            $purchaseReturn->submitted_at = $purchaseReturn->submitted_at ?? Carbon::now();
            $purchaseReturn->approved_at = null;
            $purchaseReturn->approved_by = null;

            return;
        }

        $purchaseReturn->status = PurchaseReturn::STATUS_DRAFT;
        $purchaseReturn->submitted_at = null;
        $purchaseReturn->approved_at = null;
        $purchaseReturn->approved_by = null;
    }

    private function generateNumber(): string
    {
        $prefix = 'PR-' . Carbon::now('Asia/Jakarta')->format('ym');
        $latest = PurchaseReturn::query()
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
            ['email' => 'system.procurement.return@webstellar.local'],
            ['name' => 'System Procurement Return', 'password' => 'password']
        )->id;
    }

    private function appendReason(?string $existing, string $label, ?string $reason): string
    {
        $detail = trim((string) $reason);
        $line = '[' . $label . '] ' . ($detail !== '' ? $detail : 'Aksi dilakukan dari workflow retur pembelian.');

        return trim(trim((string) $existing) . PHP_EOL . $line);
    }
}
