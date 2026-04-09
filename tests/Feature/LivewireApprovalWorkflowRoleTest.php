<?php

use App\Livewire\Operations\PurchaseOrderManagerPanel;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Workflows\PurchaseOrderWorkflow;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed();
});

test('warehouse manager can submit and approve purchase order from livewire table actions', function () {
    $warehouseManager = User::query()->where('email', 'warehouse@webstellar.local')->firstOrFail();
    $purchaseOrder = createPurchaseOrderForLivewireApproval('draft');

    $warehouseManager->update([
        'tenant_id' => $purchaseOrder->tenant_id,
        'location_id' => $purchaseOrder->location_id,
    ]);

    Livewire::actingAs($warehouseManager)
        ->test(PurchaseOrderManagerPanel::class)
        ->call('submitPurchaseOrder', $purchaseOrder->id)
        ->assertHasNoErrors();

    $purchaseOrder->refresh();
    expect($purchaseOrder->status)->toBe(PurchaseOrder::STATUS_PENDING_APPROVAL);

    Livewire::actingAs($warehouseManager)
        ->test(PurchaseOrderManagerPanel::class)
        ->call('approvePurchaseOrder', $purchaseOrder->id)
        ->assertHasNoErrors();

    $purchaseOrder->refresh();
    expect($purchaseOrder->status)->toBe(PurchaseOrder::STATUS_APPROVED);
});

test('warehouse manager can reject purchase order from livewire table actions', function () {
    $warehouseManager = User::query()->where('email', 'warehouse@webstellar.local')->firstOrFail();
    $purchaseOrder = createPurchaseOrderForLivewireApproval('submit');

    $warehouseManager->update([
        'tenant_id' => $purchaseOrder->tenant_id,
        'location_id' => $purchaseOrder->location_id,
    ]);

    Livewire::actingAs($warehouseManager)
        ->test(PurchaseOrderManagerPanel::class)
        ->call('rejectPurchaseOrder', $purchaseOrder->id)
        ->assertHasNoErrors();

    $purchaseOrder->refresh();
    expect($purchaseOrder->status)->toBe(PurchaseOrder::STATUS_REJECTED);
});

test('cashier is forbidden to run purchase order approval livewire actions', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();
    $purchaseOrder = createPurchaseOrderForLivewireApproval('submit');

    $cashier->update([
        'tenant_id' => $purchaseOrder->tenant_id,
        'location_id' => $purchaseOrder->location_id,
    ]);

    Livewire::actingAs($cashier)
        ->test(PurchaseOrderManagerPanel::class)
        ->call('approvePurchaseOrder', $purchaseOrder->id)
        ->assertForbidden();

    $purchaseOrder->refresh();
    expect($purchaseOrder->status)->toBe(PurchaseOrder::STATUS_PENDING_APPROVAL);
});

function createPurchaseOrderForLivewireApproval(string $intent): PurchaseOrder
{
    $supplier = Supplier::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $warehouse = Warehouse::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $products = \App\Models\Product::query()->where('status', \App\Models\Product::STATUS_ACTIVE)->limit(2)->get();

    $workflow = app(PurchaseOrderWorkflow::class);

    return $workflow->store(
        attributes: [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now('Asia/Jakarta')->toDateString(),
            'expected_date' => now('Asia/Jakarta')->addDays(2)->toDateString(),
            'terms' => 'Livewire approval regression',
            'notes' => 'Testing livewire PO actions',
        ],
        items: [
            [
                'product_id' => $products[0]->id,
                'ordered_quantity' => 5,
                'unit_cost' => 12000,
                'discount_amount' => 0,
                'notes' => null,
            ],
            [
                'product_id' => $products[1]->id,
                'ordered_quantity' => 3,
                'unit_cost' => 9000,
                'discount_amount' => 0,
                'notes' => null,
            ],
        ],
        intent: $intent,
    );
}
