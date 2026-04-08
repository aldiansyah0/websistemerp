<?php

namespace App\Services;

use App\Models\InventoryLedger;
use Carbon\CarbonInterface;

class InventoryPostingService
{
    public function __construct(
        private readonly StockService $stockService,
    ) {
    }

    public function currentBalance(int $productId, int $warehouseId): float
    {
        return $this->stockService->currentBalance($productId, $warehouseId);
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
    ): InventoryLedger {
        return $this->stockService->post(
            productId: $productId,
            warehouseId: $warehouseId,
            movementType: $movementType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            quantity: $quantity,
            unitCost: $unitCost,
            notes: $notes,
            transactionAt: $transactionAt,
        );
    }
}
