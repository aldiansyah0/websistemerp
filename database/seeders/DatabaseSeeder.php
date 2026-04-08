<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::query()->where('code', 'default')->value('id');
        $locationId = Location::query()->orderBy('id')->value('id');

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'ERP Admin',
                'password' => 'password',
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
            ]
        );

        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            OutletSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            InventoryLedgerSeeder::class,
            PurchaseOrderSeeder::class,
            EmployeeSeeder::class,
            AttendanceSeeder::class,
            ShiftSeeder::class,
            EmployeeShiftAssignmentSeeder::class,
            PayrollSeeder::class,
            PaymentMethodSeeder::class,
            SalesTransactionSeeder::class,
            PurchaseOrderPaymentSeeder::class,
            TenantLocationSyncSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
