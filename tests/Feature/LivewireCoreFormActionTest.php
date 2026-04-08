<?php

use App\Livewire\Operations\CategoryFormPanel;
use App\Livewire\Operations\PosTransactionFormPanel;
use App\Livewire\Operations\ProductFormPanel;
use App\Livewire\Operations\PurchaseOrderFormPanel;
use App\Livewire\Operations\SupplierFormPanel;
use App\Models\Category;
use App\Models\Employee;
use App\Models\InventoryLedger;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesTransaction;
use App\Models\Supplier;
use App\Models\Warehouse;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed();
});

test('livewire category form creates category', function () {
    Livewire::test(CategoryFormPanel::class)
        ->set('code', 'KAT-LW-01')
        ->set('name', 'Kategori Livewire')
        ->set('description', 'Kategori test livewire.')
        ->set('sort_order', 15)
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('kategori'));

    $this->assertDatabaseHas('categories', [
        'code' => 'KAT-LW-01',
        'name' => 'Kategori Livewire',
        'is_active' => true,
    ]);
});

test('livewire supplier form creates supplier', function () {
    Livewire::test(SupplierFormPanel::class)
        ->set('code', 'SUP-LW-01')
        ->set('name', 'Supplier Livewire')
        ->set('contact_person', 'Rina')
        ->set('email', 'supplier.livewire@example.test')
        ->set('phone', '081200000001')
        ->set('city', 'Jakarta')
        ->set('lead_time_days', 3)
        ->set('payment_term_days', 14)
        ->set('fill_rate', 95.5)
        ->set('reject_rate', 1.1)
        ->set('rating', 4.2)
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('supplier'));

    $this->assertDatabaseHas('suppliers', [
        'code' => 'SUP-LW-01',
        'name' => 'Supplier Livewire',
    ]);
});

test('livewire product form creates product', function () {
    $category = Category::query()->firstOrFail();
    $supplier = Supplier::query()->firstOrFail();

    Livewire::test(ProductFormPanel::class)
        ->set('category_id', $category->id)
        ->set('primary_supplier_id', $supplier->id)
        ->set('sku', 'SKU-LW-001')
        ->set('barcode', '8999912345001')
        ->set('name', 'Produk Livewire')
        ->set('description', 'Produk test livewire.')
        ->set('unit_of_measure', 'pcs')
        ->set('cost_price', 10000)
        ->set('selling_price', 15000)
        ->set('daily_run_rate', 5)
        ->set('reorder_level', 10)
        ->set('reorder_quantity', 20)
        ->set('shelf_life_days', 120)
        ->set('status', Product::STATUS_ACTIVE)
        ->set('is_featured', true)
        ->call('save')
        ->assertRedirect(route('produk'));

    $this->assertDatabaseHas('products', [
        'sku' => 'SKU-LW-001',
        'name' => 'Produk Livewire',
    ]);
});

test('livewire purchase order form submits workflow', function () {
    $supplier = Supplier::query()->firstOrFail();
    $warehouse = Warehouse::query()->firstOrFail();
    $products = Product::query()->limit(2)->get();

    Livewire::test(PurchaseOrderFormPanel::class)
        ->set('supplier_id', $supplier->id)
        ->set('warehouse_id', $warehouse->id)
        ->set('order_date', '2026-04-08')
        ->set('expected_date', '2026-04-10')
        ->set('terms', 'Test terms')
        ->set('notes', 'PO Livewire')
        ->set('items', [
            [
                'product_id' => (string) $products[0]->id,
                'ordered_quantity' => 10,
                'unit_cost' => 12000,
                'discount_amount' => 0,
                'notes' => '',
            ],
            [
                'product_id' => (string) $products[1]->id,
                'ordered_quantity' => 8,
                'unit_cost' => 8000,
                'discount_amount' => 500,
                'notes' => '',
            ],
        ])
        ->call('submitForApproval')
        ->assertRedirect(route('purchase-orders'));

    expect(PurchaseOrder::query()->latest('id')->firstOrFail()->status)
        ->toBe(PurchaseOrder::STATUS_PENDING_APPROVAL);
});

test('livewire pos form posts transaction', function () {
    [$outlet, $product, $paymentMethod, $cashier] = fixturePosLivewireContext();

    Livewire::test(PosTransactionFormPanel::class)
        ->set('outlet_id', $outlet->id)
        ->set('cashier_employee_id', $cashier?->id)
        ->set('sold_at', now()->toDateTimeString())
        ->set('due_date', now()->toDateString())
        ->set('items', [
            [
                'product_id' => (string) $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->selling_price,
                'discount_amount' => 0,
                'notes' => '',
            ],
        ])
        ->set('payments', [
            [
                'payment_method_id' => (string) $paymentMethod->id,
                'amount' => (float) $product->selling_price,
                'reference_number' => 'POS-LW-REF-01',
                'approval_code' => '',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('pos-transactions'));

    $transaction = SalesTransaction::query()->latest('id')->firstOrFail();

    $this->assertDatabaseHas('transaction_payments', [
        'transaction_id' => $transaction->id,
        'reference_number' => 'POS-LW-REF-01',
    ]);
});

function fixturePosLivewireContext(): array
{
    $outlet = Outlet::query()->whereNotNull('warehouse_id')->orderBy('id')->firstOrFail();
    $productId = InventoryLedger::query()
        ->select('product_id')
        ->where('warehouse_id', $outlet->warehouse_id)
        ->groupBy('product_id')
        ->havingRaw('SUM(quantity) > 0')
        ->value('product_id');
    if ($productId === null) {
        $productId = Product::query()->orderBy('id')->value('id');
    }
    $product = Product::query()->findOrFail($productId);
    $paymentMethod = PaymentMethod::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $cashier = Employee::query()->where('status', Employee::STATUS_ACTIVE)->orderBy('id')->first();

    return [$outlet, $product, $paymentMethod, $cashier];
}
