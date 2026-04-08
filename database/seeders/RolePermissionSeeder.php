<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'Akses dashboard dan monitoring KPI.'],
            ['name' => 'Manage Master Data', 'slug' => 'master-data.manage', 'description' => 'Kelola master produk, kategori, supplier, dan outlet.'],
            ['name' => 'Manage POS Transaction', 'slug' => 'sales.pos.manage', 'description' => 'Buat dan posting transaksi POS.'],
            ['name' => 'Manage Employee', 'slug' => 'hr.employee.manage', 'description' => 'Kelola data karyawan dan mapping lokasi.'],
            ['name' => 'Manage Shift Attendance', 'slug' => 'hr.shift.manage', 'description' => 'Jadwalkan shift dan proses absensi.'],
            ['name' => 'Manage Payroll Draft', 'slug' => 'hr.payroll.manage', 'description' => 'Generate payroll dan submit ke approval.'],
            ['name' => 'Approve Payroll Finance', 'slug' => 'finance.payroll.approve', 'description' => 'Approve/pay payroll dari sisi finance.'],
            ['name' => 'Export Financial Report', 'slug' => 'finance.report.export', 'description' => 'Export laporan laba rugi, neraca, dan jurnal.'],
            ['name' => 'Manage Stock Transfer', 'slug' => 'inventory.transfer.manage', 'description' => 'Kelola mutasi stok antar lokasi.'],
            ['name' => 'Manage Stock Opname', 'slug' => 'inventory.opname.manage', 'description' => 'Kelola stock opname dan adjustment approval.'],
            ['name' => 'Manage Procurement', 'slug' => 'procurement.purchase.manage', 'description' => 'Kelola purchase order, receiving, dan pembayaran supplier.'],
            ['name' => 'Manage Sales Return', 'slug' => 'sales.return.manage', 'description' => 'Proses retur penjualan/refund dan reverse jurnal.'],
            ['name' => 'Manage Purchase Return', 'slug' => 'procurement.return.manage', 'description' => 'Proses retur pembelian ke supplier.'],
            ['name' => 'Close Finance Period', 'slug' => 'finance.period.close', 'description' => 'Tutup atau buka periode posting keuangan.'],
            ['name' => 'Manage Cash Reconciliation', 'slug' => 'finance.reconciliation.manage', 'description' => 'Kelola rekonsiliasi kas/bank harian.'],
            ['name' => 'View Audit Trail', 'slug' => 'audit.log.view', 'description' => 'Akses audit trail untuk monitoring approval dan posting kritikal.'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $ownerRole = Role::query()->updateOrCreate(
            ['slug' => Role::OWNER],
            [
                'name' => 'Owner',
                'description' => 'Akses penuh seluruh modul ERP.',
                'is_system' => true,
            ]
        );
        $cashierRole = Role::query()->updateOrCreate(
            ['slug' => Role::CASHIER],
            [
                'name' => 'Cashier',
                'description' => 'Akses operasional kasir dan POS.',
                'is_system' => true,
            ]
        );
        $warehouseRole = Role::query()->updateOrCreate(
            ['slug' => Role::WAREHOUSE_MANAGER],
            [
                'name' => 'Warehouse Manager',
                'description' => 'Akses operasional gudang, transfer, dan procurement.',
                'is_system' => true,
            ]
        );
        $financeRole = Role::query()->updateOrCreate(
            ['slug' => Role::FINANCE],
            [
                'name' => 'Finance',
                'description' => 'Akses kontrol keuangan dan persetujuan finansial.',
                'is_system' => true,
            ]
        );

        $ownerRole->permissions()->sync(Permission::query()->pluck('id')->all());
        $cashierRole->permissions()->sync(
            Permission::query()
                ->whereIn('slug', ['dashboard.view', 'sales.pos.manage', 'sales.return.manage'])
                ->pluck('id')
                ->all()
        );
        $warehouseRole->permissions()->sync(
            Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'inventory.transfer.manage',
                    'inventory.opname.manage',
                    'procurement.purchase.manage',
                    'procurement.return.manage',
                ])
                ->pluck('id')
                ->all()
        );
        $financeRole->permissions()->sync(
            Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'finance.payroll.approve',
                    'finance.report.export',
                    'finance.period.close',
                    'finance.reconciliation.manage',
                    'audit.log.view',
                ])
                ->pluck('id')
                ->all()
        );

        $tenantId = Tenant::query()->where('code', 'default')->value('id');
        $defaultLocationId = Location::query()->orderBy('id')->value('id');

        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@webstellar.local'],
            [
                'name' => 'Owner ERP',
                'password' => 'password',
                'tenant_id' => $tenantId,
                'location_id' => $defaultLocationId,
            ]
        );
        $cashier = User::query()->updateOrCreate(
            ['email' => 'cashier@webstellar.local'],
            [
                'name' => 'Kasir Outlet',
                'password' => 'password',
                'tenant_id' => $tenantId,
                'location_id' => $defaultLocationId,
            ]
        );
        $warehouseManager = User::query()->updateOrCreate(
            ['email' => 'warehouse@webstellar.local'],
            [
                'name' => 'Manager Gudang',
                'password' => 'password',
                'tenant_id' => $tenantId,
                'location_id' => $defaultLocationId,
            ]
        );
        $financeUser = User::query()->updateOrCreate(
            ['email' => 'finance@webstellar.local'],
            [
                'name' => 'Finance ERP',
                'password' => 'password',
                'tenant_id' => $tenantId,
                'location_id' => $defaultLocationId,
            ]
        );

        $owner->roles()->syncWithoutDetaching([$ownerRole->id]);
        $cashier->roles()->syncWithoutDetaching([$cashierRole->id]);
        $warehouseManager->roles()->syncWithoutDetaching([$warehouseRole->id]);
        $financeUser->roles()->syncWithoutDetaching([$financeRole->id]);

        User::query()
            ->where('email', 'test@example.com')
            ->get()
            ->each(fn (User $user) => $user->roles()->syncWithoutDetaching([$ownerRole->id]));
    }
}
