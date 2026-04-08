<?php

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;

beforeEach(function (): void {
    $this->seed();
});

test('stock service posts inventory ledger and stock mutation mirror', function () {
    $product = Product::query()->firstOrFail();
    $warehouse = Warehouse::query()->firstOrFail();
    $service = app(StockService::class);

    $ledger = $service->post(
        productId: $product->id,
        warehouseId: $warehouse->id,
        movementType: 'purchase',
        referenceType: 'unit_test',
        referenceId: 101,
        quantity: 5,
        unitCost: (float) $product->cost_price,
        notes: 'Unit test posting',
        transactionAt: now(),
    );

    $this->assertDatabaseHas('inventory_ledgers', [
        'id' => $ledger->id,
        'movement_type' => 'purchase',
        'tenant_id' => $warehouse->tenant_id,
        'location_id' => $warehouse->location_id,
    ]);

    if ($warehouse->location_id !== null) {
        $this->assertDatabaseHas('stock_mutations', [
            'reference_type' => 'unit_test',
            'reference_id' => 101,
            'location_id' => $warehouse->location_id,
            'mutation_type' => 'in',
        ]);
    }
});

test('stock service blocks outbound mutation when projected stock is negative', function () {
    $product = Product::query()->firstOrFail();
    $warehouse = Warehouse::query()->firstOrFail();
    $service = app(StockService::class);
    $current = $service->currentBalance($product->id, $warehouse->id);

    expect(function () use ($service, $product, $warehouse, $current): void {
        $service->post(
            productId: $product->id,
            warehouseId: $warehouse->id,
            movementType: 'sale',
            referenceType: 'unit_test',
            referenceId: 102,
            quantity: -1 * ($current + 9999),
            unitCost: (float) $product->cost_price,
            notes: 'Negative stock guard',
            transactionAt: now(),
        );
    })->toThrow(\DomainException::class);
});
