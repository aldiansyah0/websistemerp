<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\PayrollRun;
use App\Models\SalesTransaction;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::query()->where('code', 'default')->value('id');
        $fallbackLocationId = Location::query()->withoutTenantLocation()->where('type', Location::TYPE_OUTLET)->orderBy('id')->value('id')
            ?? Location::query()->withoutTenantLocation()->where('type', Location::TYPE_WAREHOUSE)->orderBy('id')->value('id');

        $payrollRun = PayrollRun::query()->updateOrCreate(
            ['code' => 'PAY-2026-03'],
            [
                'tenant_id' => $tenantId,
                'location_id' => $fallbackLocationId,
                'period_start' => '2026-03-01',
                'period_end' => '2026-03-31',
                'status' => PayrollRun::STATUS_APPROVED,
                'processed_at' => CarbonImmutable::parse('2026-04-03 10:00'),
                'approved_at' => CarbonImmutable::parse('2026-04-05 15:00'),
                'paid_at' => null,
                'notes' => 'Payroll Maret siap dibayar bertahap.',
            ]
        );

        $payrollRun->items()->delete();

        $items = Employee::query()
            ->where('status', Employee::STATUS_ACTIVE)
            ->get()
            ->map(function (Employee $employee) use ($payrollRun) {
                $allowance = match ($employee->department) {
                    'Retail Operations' => 550_000,
                    'Warehouse Ops' => 450_000,
                    default => 750_000,
                };

                $overtime = (float) $employee->overtime_rate * match ($employee->employment_type) {
                    'permanent' => 6,
                    'contract' => 4,
                    'part_time' => 3,
                    default => 2,
                };

                $salesTotal = (float) SalesTransaction::query()
                    ->where('status', 'paid')
                    ->where('cashier_employee_id', $employee->id)
                    ->whereBetween('sold_at', ['2026-03-01 00:00:00', '2026-03-31 23:59:59'])
                    ->sum('net_amount');
                $salesBonus = ($salesTotal * (float) $employee->sales_bonus_rate) / 100;
                $lateMinutes = (float) AttendanceLog::query()
                    ->where('employee_id', $employee->id)
                    ->whereBetween('shift_date', ['2026-03-01', '2026-03-31'])
                    ->sum('late_minutes');
                $absenceCount = (float) AttendanceLog::query()
                    ->where('employee_id', $employee->id)
                    ->whereBetween('shift_date', ['2026-03-01', '2026-03-31'])
                    ->where('attendance_status', 'absent')
                    ->count();
                $lateDeduction = $lateMinutes * (float) $employee->late_penalty_per_minute;
                $absenceDeduction = $absenceCount * (float) $employee->absence_penalty_amount;
                $attendanceDeduction = $lateDeduction + $absenceDeduction;
                $deduction = $attendanceDeduction > 0 ? $attendanceDeduction : (float) $employee->base_salary * 0.025;
                $netSalary = (float) $employee->base_salary + $allowance + $overtime + $salesBonus - $deduction;

                return $payrollRun->items()->create([
                    'employee_id' => $employee->id,
                    'base_salary' => $employee->base_salary,
                    'allowance_amount' => $allowance,
                    'overtime_amount' => $overtime,
                    'sales_bonus_amount' => $salesBonus,
                    'deduction_amount' => $deduction,
                    'attendance_deduction_amount' => $attendanceDeduction,
                    'late_deduction_amount' => $lateDeduction,
                    'absence_deduction_amount' => $absenceDeduction,
                    'net_salary' => $netSalary,
                    'payment_status' => 'approved',
                    'notes' => 'Seed payroll item',
                ]);
            });

        $payrollRun->recalculateTotals($items);
        $payrollRun->save();
    }
}
