<?php

use App\Helpers\MenuHelper;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;

beforeEach(function (): void {
    $this->seed();
});

test('the dashboard renders live retail erp control tower content', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Executive Dashboard Retail')
        ->assertSee('Replenishment Queue')
        ->assertSee('Outlet Pulse')
        ->assertSee('Workforce Readiness')
        ->assertSee('Live Modules')
        ->assertSee('Supplier Snapshot');
});

test('all sidebar pages are reachable', function () {
    collect(MenuHelper::getPaths())->each(function (string $path) {
        $this->get($path)->assertOk();
    });
});

test('category and supplier management forms are reachable', function () {
    $category = Category::query()->firstOrFail();
    $supplier = Supplier::query()->firstOrFail();

    $this->get(route('categories.create'))->assertOk();
    $this->get(route('categories.edit', $category))->assertOk();
    $this->get(route('suppliers.create'))->assertOk();
    $this->get(route('suppliers.edit', $supplier))->assertOk();
});

test('user can create a product through the master product form', function () {
    $category = Category::query()->firstOrFail();
    $supplier = Supplier::query()->firstOrFail();

    $this->post('/produk', [
        'category_id' => $category->id,
        'primary_supplier_id' => $supplier->id,
        'sku' => 'SNK-9999',
        'barcode' => '8999000009999',
        'name' => 'Granola Honey Bites',
        'description' => 'Snack bar untuk display checkout.',
        'unit_of_measure' => 'pcs',
        'cost_price' => 8500,
        'selling_price' => 12500,
        'daily_run_rate' => 7,
        'reorder_level' => 14,
        'reorder_quantity' => 28,
        'shelf_life_days' => 180,
        'status' => Product::STATUS_ACTIVE,
        'is_featured' => 1,
    ])->assertRedirect(route('produk'));

    $this->assertDatabaseHas('products', [
        'sku' => 'SNK-9999',
        'name' => 'Granola Honey Bites',
        'status' => Product::STATUS_ACTIVE,
    ]);
});

test('user can create a category through the category management form', function () {
    $this->post('/kategori', [
        'code' => 'KAT-BABY',
        'name' => 'Baby Care',
        'description' => 'Kategori untuk perlengkapan bayi dan kebutuhan keluarga muda.',
        'sort_order' => 8,
        'is_active' => 1,
    ])->assertRedirect(route('kategori'));

    $this->assertDatabaseHas('categories', [
        'code' => 'KAT-BABY',
        'name' => 'Baby Care',
        'is_active' => true,
    ]);
});

test('user can create a supplier through the supplier management form', function () {
    $this->post('/supplier', [
        'code' => 'SUP-999',
        'name' => 'PT Mitra Cepat Distribusi',
        'contact_person' => 'Dian Puspita',
        'email' => 'ops@mitracepat.test',
        'phone' => '021-7788-1001',
        'city' => 'Jakarta',
        'address' => 'Jl. Distribusi Utama No. 12, Jakarta',
        'lead_time_days' => 5,
        'payment_term_days' => 21,
        'fill_rate' => 95.2,
        'reject_rate' => 1.1,
        'rating' => 4.3,
        'is_active' => 1,
        'notes' => 'Supplier fast moving FMCG.',
    ])->assertRedirect(route('supplier'));

    $this->assertDatabaseHas('suppliers', [
        'code' => 'SUP-999',
        'name' => 'PT Mitra Cepat Distribusi',
        'is_active' => true,
    ]);
});

test('purchase order can be created and approved through the workflow', function () {
    $supplier = Supplier::query()->firstOrFail();
    $warehouse = Warehouse::query()->firstOrFail();
    $products = Product::query()->limit(2)->get();

    $this->post('/procurement/purchase-order', [
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
        'order_date' => '2026-04-08',
        'expected_date' => '2026-04-11',
        'terms' => 'Pembayaran 21 hari.',
        'notes' => 'PO test workflow.',
        'intent' => 'submit',
        'items' => [
            [
                'product_id' => $products[0]->id,
                'ordered_quantity' => 24,
                'unit_cost' => 12000,
                'discount_amount' => 0,
                'notes' => 'Fast mover outlet.',
            ],
            [
                'product_id' => $products[1]->id,
                'ordered_quantity' => 12,
                'unit_cost' => 18000,
                'discount_amount' => 10000,
                'notes' => 'Safety stock.',
            ],
        ],
    ])->assertRedirect(route('purchase-orders'));

    $purchaseOrder = PurchaseOrder::query()->latest('id')->firstOrFail();

    expect($purchaseOrder->status)->toBe(PurchaseOrder::STATUS_PENDING_APPROVAL);

    $this->post(route('purchase-orders.approve', $purchaseOrder))
        ->assertRedirect(route('purchase-orders'));

    $purchaseOrder->refresh();

    expect($purchaseOrder->status)->toBe(PurchaseOrder::STATUS_APPROVED)
        ->and($purchaseOrder->approved_at)->not->toBeNull()
        ->and($purchaseOrder->total_amount)->toBeGreaterThan(0);
});

test('user can create an outlet through the multi outlet form', function () {
    $warehouse = Warehouse::query()->firstOrFail();

    $this->post('/outlet', [
        'code' => 'OTL-DPS01',
        'name' => 'Denpasar Sunset Road',
        'region' => 'Central',
        'city' => 'Denpasar',
        'phone' => '0361-700100',
        'manager_name' => 'Ayu Lestari',
        'warehouse_id' => $warehouse->id,
        'opening_date' => '2026-04-01',
        'status' => Outlet::STATUS_ACTIVE,
        'daily_sales_target' => 21500000,
        'service_level' => 96.4,
        'inventory_accuracy' => 98.2,
        'is_fulfillment_hub' => 1,
        'address' => 'Jl. Sunset Road No. 88, Denpasar',
    ])->assertRedirect(route('outlet'));

    $this->assertDatabaseHas('outlets', [
        'code' => 'OTL-DPS01',
        'name' => 'Denpasar Sunset Road',
        'status' => Outlet::STATUS_ACTIVE,
    ]);
});

test('user can create an employee through the hr module form', function () {
    $outlet = Outlet::query()->where('status', Outlet::STATUS_ACTIVE)->firstOrFail();

    $this->post('/hrd/kelola-karyawan', [
        'outlet_id' => $outlet->id,
        'employee_code' => 'EMP-099',
        'full_name' => 'Raka Pratama',
        'email' => 'raka.pratama@example.test',
        'phone' => '081234567890',
        'department' => 'Retail Operations',
        'position_title' => 'Store Supervisor',
        'employment_type' => 'permanent',
        'join_date' => '2026-04-08',
        'base_salary' => 7200000,
        'overtime_rate' => 35000,
        'status' => Employee::STATUS_ACTIVE,
        'emergency_contact' => 'Mira 081299900000',
        'notes' => 'Diproyeksikan memimpin outlet baru.',
    ])->assertRedirect(route('employee-management'));

    $this->assertDatabaseHas('employees', [
        'employee_code' => 'EMP-099',
        'full_name' => 'Raka Pratama',
        'status' => Employee::STATUS_ACTIVE,
    ]);
});
