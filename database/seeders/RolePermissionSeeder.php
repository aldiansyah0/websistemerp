<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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
            ['name' => 'Manage System Config', 'slug' => 'system.config.manage', 'description' => 'Kelola konfigurasi sistem tingkat lanjut dan parameter global.'],
            ['name' => 'Manage User Access', 'slug' => 'security.user.manage', 'description' => 'Kelola role, permission, dan assignment akses lokasi user.'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $roles = [
            Role::OWNER => ['name' => 'Owner', 'description' => 'Akses penuh lintas outlet dan seluruh modul ERP.'],
            Role::MANAGER => ['name' => 'Manager', 'description' => 'Pengawas operasional regional/multi outlet.'],
            Role::HRD => ['name' => 'HRD', 'description' => 'Kelola karyawan, absensi, payroll, dan mutasi antar outlet.'],
            Role::SUPER_ADMIN => ['name' => 'Super Admin', 'description' => 'Role teknis untuk konfigurasi sistem dan hak akses.'],
            Role::ADMIN => ['name' => 'Admin Cabang', 'description' => 'Penanggung jawab operasional satu outlet/cabang.'],
            Role::STAFF_ADMIN => ['name' => 'Staff Admin', 'description' => 'Input data administratif operasional outlet.'],
            Role::CASHIER => ['name' => 'Cashier', 'description' => 'Akses operasional kasir dan transaksi POS.'],
            Role::STAFF_OUTLET => ['name' => 'Staff Outlet', 'description' => 'Fokus ke aktivitas inventory fisik dan penerimaan mutasi.'],
            Role::WAREHOUSE_MANAGER => ['name' => 'Warehouse Manager', 'description' => 'Akses operasional gudang, transfer, dan procurement.'],
            Role::FINANCE => ['name' => 'Finance', 'description' => 'Akses kontrol keuangan dan persetujuan finansial.'],
        ];

        $roleModels = collect($roles)->mapWithKeys(function (array $meta, string $slug): array {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                    'is_system' => true,
                ]
            );

            return [$slug => $role];
        });

        $permissionMap = [
            Role::OWNER => Permission::query()->pluck('id')->all(),
            Role::SUPER_ADMIN => Permission::query()->pluck('id')->all(),
            Role::MANAGER => Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'master-data.manage',
                    'inventory.transfer.manage',
                    'inventory.opname.manage',
                    'procurement.purchase.manage',
                    'procurement.return.manage',
                    'sales.pos.manage',
                    'sales.return.manage',
                    'finance.report.export',
                    'finance.reconciliation.manage',
                ])
                ->pluck('id')
                ->all(),
            Role::HRD => Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'hr.employee.manage',
                    'hr.shift.manage',
                    'hr.payroll.manage',
                ])
                ->pluck('id')
                ->all(),
            Role::ADMIN => Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'sales.pos.manage',
                    'inventory.transfer.manage',
                    'inventory.opname.manage',
                    'procurement.purchase.manage',
                    'procurement.return.manage',
                    'sales.return.manage',
                    'hr.shift.manage',
                    'finance.reconciliation.manage',
                ])
                ->pluck('id')
                ->all(),
            Role::STAFF_ADMIN => Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'inventory.transfer.manage',
                    'procurement.purchase.manage',
                    'hr.shift.manage',
                ])
                ->pluck('id')
                ->all(),
            Role::CASHIER => Permission::query()
                ->whereIn('slug', ['dashboard.view', 'sales.pos.manage', 'sales.return.manage'])
                ->pluck('id')
                ->all(),
            Role::STAFF_OUTLET => Permission::query()
                ->whereIn('slug', ['dashboard.view', 'inventory.transfer.manage'])
                ->pluck('id')
                ->all(),
            Role::WAREHOUSE_MANAGER => Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'inventory.transfer.manage',
                    'inventory.opname.manage',
                    'procurement.purchase.manage',
                    'procurement.return.manage',
                ])
                ->pluck('id')
                ->all(),
            Role::FINANCE => Permission::query()
                ->whereIn('slug', [
                    'dashboard.view',
                    'finance.payroll.approve',
                    'finance.report.export',
                    'finance.period.close',
                    'finance.reconciliation.manage',
                    'audit.log.view',
                ])
                ->pluck('id')
                ->all(),
        ];

        foreach ($permissionMap as $roleSlug => $permissionIds) {
            $role = $roleModels->get($roleSlug);
            if ($role instanceof Role) {
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
