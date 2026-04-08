<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Location;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = Outlet::query()
            ->select('id', 'code', 'location_id')
            ->get()
            ->keyBy('code');
        $warehouseLocationId = Location::query()
            ->where('type', Location::TYPE_WAREHOUSE)
            ->orderBy('id')
            ->value('id');

        $employees = [
            ['code' => 'EMP-001', 'name' => 'Nadia Putri', 'email' => 'nadia@webstellar.local', 'phone' => '0812000001', 'department' => 'Retail Operations', 'position' => 'Store Manager', 'employment_type' => 'permanent', 'join_date' => '2021-06-10', 'base_salary' => 9_500_000, 'overtime_rate' => 55_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-JKT01'],
            ['code' => 'EMP-002', 'name' => 'Fadli Ramadhan', 'email' => 'fadli@webstellar.local', 'phone' => '0812000002', 'department' => 'Retail Operations', 'position' => 'Senior Cashier', 'employment_type' => 'permanent', 'join_date' => '2022-01-08', 'base_salary' => 5_400_000, 'overtime_rate' => 32_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-JKT01'],
            ['code' => 'EMP-003', 'name' => 'Gita Maheswari', 'email' => 'gita@webstellar.local', 'phone' => '0812000003', 'department' => 'Retail Operations', 'position' => 'Store Supervisor', 'employment_type' => 'contract', 'join_date' => '2023-03-12', 'base_salary' => 6_200_000, 'overtime_rate' => 35_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-BDG01'],
            ['code' => 'EMP-004', 'name' => 'Rizky Aulia', 'email' => 'rizky@webstellar.local', 'phone' => '0812000004', 'department' => 'Retail Operations', 'position' => 'Cashier', 'employment_type' => 'part_time', 'join_date' => '2024-02-01', 'base_salary' => 3_200_000, 'overtime_rate' => 24_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-BDG01'],
            ['code' => 'EMP-005', 'name' => 'Budi Santoso', 'email' => 'budi@webstellar.local', 'phone' => '0812000005', 'department' => 'Retail Operations', 'position' => 'Store Manager', 'employment_type' => 'permanent', 'join_date' => '2021-11-05', 'base_salary' => 8_900_000, 'overtime_rate' => 50_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-SBY01'],
            ['code' => 'EMP-006', 'name' => 'Tika Rahma', 'email' => 'tika@webstellar.local', 'phone' => '0812000006', 'department' => 'Retail Operations', 'position' => 'Visual Merchandiser', 'employment_type' => 'contract', 'join_date' => '2023-07-19', 'base_salary' => 4_700_000, 'overtime_rate' => 28_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-SBY01'],
            ['code' => 'EMP-007', 'name' => 'Angga Saputra', 'email' => 'angga@webstellar.local', 'phone' => '0812000007', 'department' => 'Retail Operations', 'position' => 'Store Supervisor', 'employment_type' => 'permanent', 'join_date' => '2023-10-02', 'base_salary' => 5_800_000, 'overtime_rate' => 31_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => 'OTL-BKS01'],
            ['code' => 'EMP-008', 'name' => 'Putri Larasati', 'email' => 'putri@webstellar.local', 'phone' => '0812000008', 'department' => 'Retail Operations', 'position' => 'Cashier', 'employment_type' => 'part_time', 'join_date' => '2024-05-15', 'base_salary' => 3_100_000, 'overtime_rate' => 22_000, 'status' => Employee::STATUS_LEAVE, 'outlet' => 'OTL-BKS01'],
            ['code' => 'EMP-009', 'name' => 'Yoga Pratama', 'email' => 'yoga@webstellar.local', 'phone' => '0812000009', 'department' => 'People Operations', 'position' => 'HR Generalist', 'employment_type' => 'permanent', 'join_date' => '2022-04-09', 'base_salary' => 6_900_000, 'overtime_rate' => 30_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => null],
            ['code' => 'EMP-010', 'name' => 'Clara Octavia', 'email' => 'clara@webstellar.local', 'phone' => '0812000010', 'department' => 'Finance', 'position' => 'Payroll Officer', 'employment_type' => 'permanent', 'join_date' => '2022-09-14', 'base_salary' => 7_400_000, 'overtime_rate' => 33_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => null],
            ['code' => 'EMP-011', 'name' => 'Rendy Kusuma', 'email' => 'rendy@webstellar.local', 'phone' => '0812000011', 'department' => 'Warehouse Ops', 'position' => 'Receiving Lead', 'employment_type' => 'contract', 'join_date' => '2023-01-05', 'base_salary' => 5_600_000, 'overtime_rate' => 29_000, 'status' => Employee::STATUS_ACTIVE, 'outlet' => null],
            ['code' => 'EMP-012', 'name' => 'Mira Kinasih', 'email' => 'mira@webstellar.local', 'phone' => '0812000012', 'department' => 'Retail Operations', 'position' => 'Store Manager', 'employment_type' => 'permanent', 'join_date' => '2024-06-20', 'base_salary' => 8_100_000, 'overtime_rate' => 45_000, 'status' => Employee::STATUS_RESIGNED, 'outlet' => 'OTL-YGY01'],
        ];

        foreach ($employees as $employee) {
            $outlet = $employee['outlet'] ? $outlets->get($employee['outlet']) : null;
            $salesBonusRate = str_contains(strtolower($employee['position']), 'cashier')
                ? 1.2
                : (str_contains(strtolower($employee['department']), 'retail') ? 0.45 : 0);

            Employee::query()->updateOrCreate(
                ['employee_code' => $employee['code']],
                [
                    'outlet_id' => $outlet?->id,
                    'location_id' => $outlet?->location_id ?? ($employee['department'] === 'Warehouse Ops' ? $warehouseLocationId : null),
                    'full_name' => $employee['name'],
                    'email' => $employee['email'],
                    'phone' => $employee['phone'],
                    'department' => $employee['department'],
                    'position_title' => $employee['position'],
                    'employment_type' => $employee['employment_type'],
                    'join_date' => $employee['join_date'],
                    'base_salary' => $employee['base_salary'],
                    'sales_bonus_rate' => $salesBonusRate,
                    'overtime_rate' => $employee['overtime_rate'],
                    'late_penalty_per_minute' => 1_500,
                    'absence_penalty_amount' => 120_000,
                    'status' => $employee['status'],
                    'emergency_contact' => '021-3000-00' . substr($employee['code'], -1),
                    'notes' => 'Data karyawan seed untuk fondasi HR retail ERP.',
                ]
            );
        }
    }
}
