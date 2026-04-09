<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\InventoryLedger;
use App\Models\Location;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\PayrollRun;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\SalesTransaction;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class TenantLocationSyncSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['code' => 'default'],
            ['name' => 'Default Tenant', 'is_active' => true]
        );

        $tenantId = (int) $tenant->id;
        $this->syncLocations($tenantId);
        $this->syncDefaultVariants($tenantId);
        $this->backfillTenantAndLocation($tenantId);
    }

    private function syncLocations(int $tenantId): void
    {
        foreach (Warehouse::query()->get() as $warehouse) {
            $location = Location::query()->updateOrCreate(
                [
                    'type' => Location::TYPE_WAREHOUSE,
                    'legacy_warehouse_id' => $warehouse->id,
                ],
                [
                    'tenant_id' => $warehouse->tenant_id ?? $tenantId,
                    'code' => $warehouse->code,
                    'name' => $warehouse->name,
                    'warehouse_subtype' => $warehouse->type,
                    'city' => $warehouse->city,
                    'address' => $warehouse->address,
                    'status' => $warehouse->is_active ? 'active' : 'inactive',
                    'is_fulfillment_hub' => false,
                ]
            );

            $warehouse->forceFill([
                'tenant_id' => $warehouse->tenant_id ?? $tenantId,
                'location_id' => $location->id,
            ])->saveQuietly();
        }

        $warehouseLocationMap = Location::query()
            ->where('type', Location::TYPE_WAREHOUSE)
            ->whereNotNull('legacy_warehouse_id')
            ->pluck('id', 'legacy_warehouse_id');

        foreach (Outlet::query()->get() as $outlet) {
            $location = Location::query()->updateOrCreate(
                [
                    'type' => Location::TYPE_OUTLET,
                    'legacy_outlet_id' => $outlet->id,
                ],
                [
                    'tenant_id' => $outlet->tenant_id ?? $tenantId,
                    'code' => $outlet->code,
                    'name' => $outlet->name,
                    'region' => $outlet->region,
                    'city' => $outlet->city,
                    'phone' => $outlet->phone,
                    'manager_name' => $outlet->manager_name,
                    'opening_date' => $outlet->opening_date,
                    'status' => $outlet->status,
                    'is_fulfillment_hub' => $outlet->is_fulfillment_hub,
                    'address' => $outlet->address,
                    'fulfillment_location_id' => $outlet->warehouse_id ? ($warehouseLocationMap[(int) $outlet->warehouse_id] ?? null) : null,
                ]
            );

            $outlet->forceFill([
                'tenant_id' => $outlet->tenant_id ?? $tenantId,
                'location_id' => $location->id,
            ])->saveQuietly();
        }
    }

    private function syncDefaultVariants(int $tenantId): void
    {
        foreach (Product::query()->get() as $product) {
            ProductVariant::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'is_default' => true,
                ],
                [
                    'tenant_id' => $product->tenant_id ?? $tenantId,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'variant_name' => 'Default',
                    'unit_of_measure' => $product->unit_of_measure,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'reorder_level' => $product->reorder_level,
                    'reorder_quantity' => $product->reorder_quantity,
                    'status' => $product->status,
                ]
            );
        }
    }

    private function backfillTenantAndLocation(int $tenantId): void
    {
        Category::query()->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        Supplier::query()->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        Product::query()->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        ProductVariant::query()->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        Customer::query()->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        PaymentMethod::query()->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);

        $warehouseLocations = Warehouse::query()->pluck('location_id', 'id');
        $warehouseTenants = Warehouse::query()->pluck('tenant_id', 'id');

        foreach (InventoryLedger::query()->get(['id', 'warehouse_id']) as $row) {
            InventoryLedger::query()->whereKey($row->id)->update([
                'tenant_id' => $warehouseTenants[(int) $row->warehouse_id] ?? $tenantId,
                'location_id' => $warehouseLocations[(int) $row->warehouse_id] ?? null,
            ]);
        }

        foreach (PurchaseOrder::query()->get(['id', 'warehouse_id']) as $row) {
            PurchaseOrder::query()->whereKey($row->id)->update([
                'tenant_id' => $warehouseTenants[(int) $row->warehouse_id] ?? $tenantId,
                'location_id' => $warehouseLocations[(int) $row->warehouse_id] ?? null,
            ]);
        }

        foreach (GoodsReceipt::query()->get(['id', 'warehouse_id']) as $row) {
            GoodsReceipt::query()->whereKey($row->id)->update([
                'tenant_id' => $warehouseTenants[(int) $row->warehouse_id] ?? $tenantId,
                'location_id' => $warehouseLocations[(int) $row->warehouse_id] ?? null,
            ]);
        }

        foreach (StockTransfer::query()->get(['id', 'source_warehouse_id']) as $row) {
            StockTransfer::query()->whereKey($row->id)->update([
                'tenant_id' => $warehouseTenants[(int) $row->source_warehouse_id] ?? $tenantId,
                'location_id' => $warehouseLocations[(int) $row->source_warehouse_id] ?? null,
            ]);
        }

        $outletLocations = Outlet::query()->pluck('location_id', 'id');
        $outletTenants = Outlet::query()->pluck('tenant_id', 'id');

        foreach (SalesTransaction::query()->get(['id', 'outlet_id']) as $row) {
            SalesTransaction::query()->whereKey($row->id)->update([
                'tenant_id' => $outletTenants[(int) $row->outlet_id] ?? $tenantId,
                'location_id' => $outletLocations[(int) $row->outlet_id] ?? null,
            ]);
        }

        foreach (Employee::query()->get(['id', 'outlet_id']) as $row) {
            Employee::query()->whereKey($row->id)->update([
                'tenant_id' => $outletTenants[(int) $row->outlet_id] ?? $tenantId,
                'location_id' => $outletLocations[(int) $row->outlet_id] ?? null,
            ]);
        }

        foreach (AttendanceLog::query()->get(['id', 'outlet_id']) as $row) {
            AttendanceLog::query()->whereKey($row->id)->update([
                'tenant_id' => $outletTenants[(int) $row->outlet_id] ?? $tenantId,
                'location_id' => $outletLocations[(int) $row->outlet_id] ?? null,
            ]);
        }

        $fallbackLocation = Outlet::query()->orderBy('id')->value('location_id') ?? Warehouse::query()->orderBy('id')->value('location_id');
        PayrollRun::query()->whereNull('tenant_id')->update([
            'tenant_id' => $tenantId,
            'location_id' => $fallbackLocation,
        ]);

        if ($fallbackLocation !== null) {
            PayrollRun::query()->whereNull('location_id')->update([
                'location_id' => $fallbackLocation,
            ]);
        }
    }
}
