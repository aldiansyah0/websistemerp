<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly AnalyticsCacheService $analyticsCacheService,
        private readonly PeriodLockService $periodLockService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function store(array $attributes, array $items, string $intent = 'draft'): StockOpname
    {
        return DB::transaction(function () use ($attributes, $items, $intent): StockOpname {
            $warehouse = Warehouse::query()->findOrFail($attributes['warehouse_id']);

            $opname = new StockOpname([
                'tenant_id' => $warehouse->tenant_id,
                'location_id' => $warehouse->location_id,
                'warehouse_id' => $warehouse->id,
                'opname_number' => $this->generateNumber(),
                'opname_date' => $attributes['opname_date'] ?? now('Asia/Jakarta')->toDateString(),
                'status' => StockOpname::STATUS_DRAFT,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->applyIntent($opname, $intent);
            $opname->save();

            $this->syncItems($opname, $items);
            $opname->save();

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('inventory', 'stock_opname.store', 'Stock opname dibuat', $opname, [
                'status' => $opname->status,
                'warehouse_id' => $opname->warehouse_id,
            ]);

            return $opname->fresh(['warehouse', 'items.product', 'items.productVariant']);
        });
    }

    public function update(StockOpname $opname, array $attributes, array $items, string $intent = 'draft'): StockOpname
    {
        if (! $opname->canBeEdited()) {
            throw new DomainException('Stock opname ini tidak bisa diubah lagi.');
        }

        return DB::transaction(function () use ($opname, $attributes, $items, $intent): StockOpname {
            $warehouse = Warehouse::query()->findOrFail($attributes['warehouse_id']);

            $opname->fill([
                'tenant_id' => $warehouse->tenant_id,
                'location_id' => $warehouse->location_id,
                'warehouse_id' => $warehouse->id,
                'opname_date' => $attributes['opname_date'] ?? $opname->opname_date,
                'notes' => $attributes['notes'] ?? $opname->notes,
            ]);

            $this->applyIntent($opname, $intent);
            $opname->save();

            $this->syncItems($opname, $items);
            $opname->save();

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('inventory', 'stock_opname.update', 'Stock opname diperbarui', $opname, [
                'status' => $opname->status,
                'warehouse_id' => $opname->warehouse_id,
            ]);

            return $opname->fresh(['warehouse', 'items.product', 'items.productVariant']);
        });
    }

    public function submit(StockOpname $opname): StockOpname
    {
        if (! in_array($opname->status, [StockOpname::STATUS_DRAFT, StockOpname::STATUS_REJECTED], true)) {
            throw new DomainException('Hanya draft atau rejected yang bisa disubmit ulang.');
        }

        $opname->status = StockOpname::STATUS_PENDING_APPROVAL;
        $opname->submitted_at = Carbon::now();
        $opname->approved_at = null;
        $opname->approved_by = null;
        $opname->save();

        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('inventory', 'stock_opname.submit', 'Stock opname dikirim ke approval', $opname, [
            'status' => $opname->status,
        ]);

        return $opname;
    }

    public function approve(StockOpname $opname): StockOpname
    {
        if (! $opname->canBeApproved()) {
            throw new DomainException('Stock opname ini tidak berada pada status pending approval.');
        }

        $this->periodLockService->assertDateIsOpen($opname->opname_date, 'Approval stock opname');
        $opname->loadMissing('items.product');

        return DB::transaction(function () use ($opname): StockOpname {
            foreach ($opname->items as $item) {
                $varianceQuantity = (float) $item->variance_quantity;
                if (abs($varianceQuantity) < 0.0001) {
                    continue;
                }

                $this->stockService->post(
                    productId: (int) $item->product_id,
                    warehouseId: (int) $opname->warehouse_id,
                    movementType: 'adjustment',
                    referenceType: 'stock_opname',
                    referenceId: (int) $opname->id,
                    quantity: $varianceQuantity,
                    unitCost: (float) $item->unit_cost,
                    notes: 'Stock opname ' . $opname->opname_number,
                    transactionAt: Carbon::parse($opname->opname_date)->endOfDay(),
                    transferStatus: null,
                    productVariantId: $item->product_variant_id ? (int) $item->product_variant_id : null,
                );
            }

            $opname->status = StockOpname::STATUS_APPROVED;
            $opname->approved_at = Carbon::now();
            $opname->approved_by = $this->actorId();
            $opname->save();

            $this->analyticsCacheService->invalidate();
            $this->auditLogService->log('inventory', 'stock_opname.approve', 'Stock opname disetujui dan diposting ke ledger', $opname, [
                'status' => $opname->status,
                'variance_qty' => (float) $opname->total_variance_qty,
            ]);

            return $opname->fresh(['warehouse', 'items.product', 'items.productVariant']);
        });
    }

    public function reject(StockOpname $opname, ?string $reason = null): StockOpname
    {
        if (! $opname->canBeApproved()) {
            throw new DomainException('Hanya stock opname pending approval yang bisa ditolak.');
        }

        $opname->status = StockOpname::STATUS_REJECTED;
        $opname->approved_at = null;
        $opname->approved_by = null;
        $opname->notes = $this->appendReason($opname->notes, 'Rejected', $reason);
        $opname->save();

        $this->analyticsCacheService->invalidate();
        $this->auditLogService->log('inventory', 'stock_opname.reject', 'Stock opname ditolak', $opname, [
            'status' => $opname->status,
            'reason' => $reason,
        ]);

        return $opname;
    }

    private function syncItems(StockOpname $opname, array $items): void
    {
        $normalizedItems = collect($items)
            ->filter(fn (array $item): bool => isset($item['product_id']) || isset($item['product_variant_id']))
            ->map(function (array $item) use ($opname): array {
                if (isset($item['product_variant_id'])) {
                    $variant = ProductVariant::query()->with('product')->findOrFail((int) $item['product_variant_id']);
                    $product = $variant->product;

                    if (! $product instanceof Product) {
                        throw new DomainException('Variant produk tidak memiliki master produk yang valid.');
                    }
                    if (isset($item['product_id']) && (int) $item['product_id'] !== (int) $product->id) {
                        throw new DomainException('Kombinasi product_id dan product_variant_id pada stock opname tidak valid.');
                    }
                } else {
                    $product = Product::query()->findOrFail((int) $item['product_id']);
                    $variant = $this->stockService->resolveProductVariant((int) $product->id);
                }

                $systemQuantity = isset($item['system_quantity'])
                    ? (float) $item['system_quantity']
                    : $this->stockService->currentVariantBalance((int) $variant->id, (int) $opname->warehouse_id);
                $physicalQuantity = (float) ($item['physical_quantity'] ?? $systemQuantity);
                $variance = $physicalQuantity - $systemQuantity;
                $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : (float) ($variant->cost_price ?? $product->cost_price);

                return [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'variance_quantity' => $variance,
                    'unit_cost' => $unitCost,
                    'variance_value' => $variance * $unitCost,
                    'notes' => $item['notes'] ?? null,
                ];
            })
            ->values();

        if ($normalizedItems->isEmpty()) {
            throw new DomainException('Stock opname minimal harus memiliki satu item.');
        }

        $opname->items()->delete();
        $opname->items()->createMany($normalizedItems->all());
        $opname->load('items');
        $opname->recalculateTotals($opname->items);
    }

    private function applyIntent(StockOpname $opname, string $intent): void
    {
        if ($intent === 'submit') {
            $opname->status = StockOpname::STATUS_PENDING_APPROVAL;
            $opname->submitted_at = $opname->submitted_at ?? Carbon::now();
            $opname->approved_at = null;
            $opname->approved_by = null;

            return;
        }

        $opname->status = StockOpname::STATUS_DRAFT;
        $opname->submitted_at = null;
        $opname->approved_at = null;
        $opname->approved_by = null;
    }

    private function generateNumber(): string
    {
        $prefix = 'SO-' . Carbon::now('Asia/Jakarta')->format('ym');
        $latest = StockOpname::query()
            ->where('opname_number', 'like', $prefix . '-%')
            ->orderByDesc('opname_number')
            ->value('opname_number');

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
            ['email' => 'system.inventory.audit@webstellar.local'],
            ['name' => 'System Inventory Auditor', 'password' => 'password']
        )->id;
    }

    private function appendReason(?string $existing, string $label, ?string $reason): string
    {
        $detail = trim((string) $reason);
        $line = '[' . $label . '] ' . ($detail !== '' ? $detail : 'Aksi dilakukan dari workflow stock opname.');

        return trim(trim((string) $existing) . PHP_EOL . $line);
    }
}
