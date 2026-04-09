<?php

namespace App\Services;

use App\Models\InventoryLedger;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMutation;
use Carbon\CarbonInterface;
use DomainException;

class StockService
{
    public function __construct(
        private readonly PeriodLockService $periodLockService,
    ) {
    }

    /**
     * @var array<int, int|null>
     */
    private array $warehouseLocationCache = [];

    /**
     * @var array<int, ProductVariant|null>
     */
    private array $defaultVariantCache = [];

    public function currentBalance(int $productId, int $warehouseId): float
    {
        return (float) InventoryLedger::query()
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }

    public function currentVariantBalance(int $productVariantId, int $warehouseId): float
    {
        return (float) InventoryLedger::query()
            ->where('product_variant_id', $productVariantId)
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }

    public function currentLocationBalance(int $productVariantId, int $locationId): float
    {
        return (float) StockMutation::query()
            ->where('product_variant_id', $productVariantId)
            ->where('location_id', $locationId)
            ->sum('quantity');
    }

    public function validateMinimumStock(
        int $productId,
        int $warehouseId,
        float $quantityDelta,
        ?float $minimumStock = null
    ): void {
        $balanceBefore = $this->currentBalance($productId, $warehouseId);
        $projectedBalance = $balanceBefore + $quantityDelta;

        if ($projectedBalance < -0.0001) {
            throw new DomainException('Stok tidak mencukupi untuk memproses pergerakan inventory ini.');
        }

        if ($quantityDelta >= 0 || ! config('erp.stock.enforce_minimum', false)) {
            return;
        }

        if ($minimumStock === null) {
            $minimumStock = (float) Product::query()->whereKey($productId)->value('reorder_level');
        }

        if ($minimumStock > 0 && $projectedBalance + 0.0001 < $minimumStock) {
            throw new DomainException(
                'Mutasi stok ditolak karena saldo akan turun di bawah minimum stock (' . number_format($minimumStock, 2, ',', '.') . ').'
            );
        }
    }

    public function post(
        int $productId,
        int $warehouseId,
        string $movementType,
        ?string $referenceType,
        ?int $referenceId,
        float $quantity,
        float $unitCost,
        ?string $notes = null,
        CarbonInterface|string|null $transactionAt = null,
        ?string $transferStatus = null,
        ?int $productVariantId = null,
    ): InventoryLedger {
        $this->periodLockService->assertDateIsOpen($transactionAt ?? now(), 'Posting stok ' . $movementType);

        $variant = $this->resolveProductVariant($productId, $productVariantId);
        $balanceBefore = $this->currentBalance($productId, $warehouseId);
        $balanceAfter = $balanceBefore + $quantity;
        $locationId = $this->warehouseLocationId($warehouseId);
        $tenantId = $this->resolveTenantId($productId, $warehouseId);

        $this->validateMinimumStock($productId, $warehouseId, $quantity);

        $ledger = InventoryLedger::query()->create([
            'tenant_id' => $tenantId,
            'location_id' => $locationId,
            'product_id' => $productId,
            'product_variant_id' => (int) $variant->id,
            'warehouse_id' => $warehouseId,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'balance_after' => $balanceAfter,
            'notes' => $notes,
            'transaction_at' => $transactionAt,
        ]);

        $this->postMutationMirror(
            productId: $productId,
            warehouseId: $warehouseId,
            movementType: $movementType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            quantity: $quantity,
            unitCost: $unitCost,
            notes: $notes,
            occurredAt: $transactionAt,
            transferStatus: $transferStatus,
            tenantId: $tenantId,
            productVariantId: (int) $variant->id,
        );

        return $ledger;
    }

    private function postMutationMirror(
        int $productId,
        int $warehouseId,
        string $movementType,
        ?string $referenceType,
        ?int $referenceId,
        float $quantity,
        float $unitCost,
        ?string $notes,
        CarbonInterface|string|null $occurredAt,
        ?string $transferStatus,
        ?int $tenantId,
        int $productVariantId,
    ): void {
        $locationId = $this->warehouseLocationId($warehouseId);

        if ($locationId === null) {
            return;
        }

        $balanceBefore = $this->currentLocationBalance($productVariantId, (int) $locationId);
        $status = $transferStatus ?? $this->defaultTransferStatus($movementType);

        StockMutation::query()->create([
            'product_variant_id' => $productVariantId,
            'location_id' => (int) $locationId,
            'mutation_type' => $this->mapMutationType($movementType, $quantity),
            'transfer_status' => $status,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'balance_after' => $balanceBefore + $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'occurred_at' => $occurredAt ?? now(),
            'tenant_id' => $tenantId,
        ]);
    }

    private function mapMutationType(string $movementType, float $quantity): string
    {
        return match ($movementType) {
            'opening', 'purchase', 'purchase_receipt', 'transfer_in', 'return_from_customer' => 'in',
            'sale', 'transfer_out', 'return_to_supplier' => 'out',
            'adjustment', 'cycle_count' => 'adjustment',
            default => $quantity >= 0 ? 'in' : 'out',
        };
    }

    private function defaultTransferStatus(string $movementType): ?string
    {
        return match ($movementType) {
            'transfer_out' => 'sent',
            'transfer_in' => 'received',
            default => null,
        };
    }

    private function warehouseLocationId(int $warehouseId): ?int
    {
        if (! array_key_exists($warehouseId, $this->warehouseLocationCache)) {
            $this->warehouseLocationCache[$warehouseId] = Location::query()
                ->where('legacy_warehouse_id', $warehouseId)
                ->value('id');
        }

        return $this->warehouseLocationCache[$warehouseId];
    }

    public function resolveProductVariant(int $productId, ?int $productVariantId = null): ProductVariant
    {
        if ($productVariantId !== null) {
            $variant = ProductVariant::query()
                ->whereKey($productVariantId)
                ->where('product_id', $productId)
                ->first();

            if ($variant !== null) {
                return $variant;
            }
        }

        $default = $this->defaultVariant($productId);
        if ($default !== null) {
            return $default;
        }

        $product = Product::query()->findOrFail($productId);

        $created = ProductVariant::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'is_default' => true,
            ],
            [
                'tenant_id' => $product->tenant_id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'variant_name' => 'Default',
                'size' => null,
                'color' => null,
                'attributes' => null,
                'unit_of_measure' => $product->unit_of_measure,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'reorder_level' => $product->reorder_level,
                'reorder_quantity' => $product->reorder_quantity,
                'status' => in_array($product->status, ['active', 'inactive', 'discontinued'], true) ? $product->status : 'active',
            ],
        );

        $this->defaultVariantCache[$productId] = $created;

        return $created;
    }

    private function defaultVariant(int $productId): ?ProductVariant
    {
        if (! array_key_exists($productId, $this->defaultVariantCache)) {
            $this->defaultVariantCache[$productId] = ProductVariant::query()
                ->where('product_id', $productId)
                ->where('is_default', true)
                ->first();
        }

        return $this->defaultVariantCache[$productId];
    }

    private function resolveTenantId(int $productId, int $warehouseId): ?int
    {
        $tenantId = auth()->user()?->tenant_id;
        if ($tenantId !== null) {
            return (int) $tenantId;
        }

        $warehouseTenant = Location::query()
            ->where('legacy_warehouse_id', $warehouseId)
            ->value('tenant_id');
        if ($warehouseTenant !== null) {
            return (int) $warehouseTenant;
        }

        $productTenant = Product::query()->whereKey($productId)->value('tenant_id');

        return $productTenant !== null ? (int) $productTenant : null;
    }
}
