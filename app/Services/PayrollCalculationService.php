<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\SalesTransaction;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
    ) {
    }

    public function generate(array $attributes): PayrollRun
    {
        $periodStart = CarbonImmutable::parse((string) $attributes['period_start'])->startOfDay();
        $periodEnd = CarbonImmutable::parse((string) $attributes['period_end'])->endOfDay();

        if ($periodEnd->lessThan($periodStart)) {
            throw new DomainException('Periode payroll tidak valid. Tanggal selesai harus >= tanggal mulai.');
        }

        return DB::transaction(function () use ($attributes, $periodStart, $periodEnd): PayrollRun {
            $employees = Employee::query()
                ->where('status', Employee::STATUS_ACTIVE)
                ->when(
                    filled($attributes['location_id'] ?? null),
                    fn ($query) => $query->where('location_id', (int) $attributes['location_id'])
                )
                ->orderBy('full_name')
                ->get();

            if ($employees->isEmpty()) {
                throw new DomainException('Tidak ada karyawan aktif pada filter lokasi yang dipilih.');
            }

            $attendanceByEmployee = $this->attendanceAggByEmployee($employees, $periodStart, $periodEnd);
            $salesByEmployee = $this->salesAggByEmployee($employees, $periodStart, $periodEnd);

            $payrollRun = PayrollRun::query()->create([
                'tenant_id' => $employees->first()?->tenant_id ?? auth()->user()?->tenant_id,
                'location_id' => $attributes['location_id'] ?? $employees->first()?->location_id,
                'code' => $this->generateCode($periodEnd),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'status' => PayrollRun::STATUS_DRAFT,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $items = $employees->map(function (Employee $employee) use ($payrollRun, $attendanceByEmployee, $salesByEmployee): object {
                $attendance = $attendanceByEmployee[$employee->id] ?? null;
                $salesTotal = (float) ($salesByEmployee[$employee->id]?->sales_total ?? 0);
                $salesBonusAmount = ($salesTotal * (float) $employee->sales_bonus_rate) / 100;
                $overtimeMinutes = (float) ($attendance?->overtime_minutes ?? 0);
                $lateMinutes = (float) ($attendance?->late_minutes ?? 0);
                $absentCount = (float) ($attendance?->absent_count ?? 0);
                $overtimeAmount = ((float) $employee->overtime_rate * $overtimeMinutes) / 60;
                $lateDeduction = (float) $employee->late_penalty_per_minute * $lateMinutes;
                $absenceDeduction = (float) $employee->absence_penalty_amount * $absentCount;
                $attendanceDeduction = $lateDeduction + $absenceDeduction;
                $deductionAmount = $attendanceDeduction;
                $netSalary = max(
                    ((float) $employee->base_salary + $salesBonusAmount + $overtimeAmount) - $deductionAmount,
                    0
                );

                return $payrollRun->items()->create([
                    'employee_id' => $employee->id,
                    'base_salary' => $employee->base_salary,
                    'allowance_amount' => 0,
                    'overtime_amount' => $overtimeAmount,
                    'sales_bonus_amount' => $salesBonusAmount,
                    'deduction_amount' => $deductionAmount,
                    'attendance_deduction_amount' => $attendanceDeduction,
                    'late_deduction_amount' => $lateDeduction,
                    'absence_deduction_amount' => $absenceDeduction,
                    'net_salary' => $netSalary,
                    'payment_status' => 'pending',
                    'notes' => 'Auto payroll: basic + bonus POS + overtime - potongan absensi.',
                ]);
            });

            $payrollRun->recalculateTotals($items);
            $payrollRun->save();
            $this->analyticsCacheService->invalidate();

            return $payrollRun->fresh(['items.employee.outlet']);
        });
    }

    private function attendanceAggByEmployee(Collection $employees, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Collection
    {
        return AttendanceLog::query()
            ->select('employee_id')
            ->selectRaw('SUM(overtime_minutes) as overtime_minutes')
            ->selectRaw('SUM(late_minutes) as late_minutes')
            ->selectRaw("SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_count")
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('shift_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');
    }

    private function salesAggByEmployee(Collection $employees, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Collection
    {
        return SalesTransaction::query()
            ->selectRaw('cashier_employee_id as employee_id')
            ->selectRaw('SUM(net_amount) as sales_total')
            ->whereIn('cashier_employee_id', $employees->pluck('id'))
            ->where('status', 'paid')
            ->whereBetween('sold_at', [$periodStart->toDateTimeString(), $periodEnd->toDateTimeString()])
            ->groupBy('cashier_employee_id')
            ->get()
            ->keyBy('employee_id');
    }

    private function generateCode(CarbonImmutable $periodEnd): string
    {
        $prefix = 'PAY-' . $periodEnd->format('Ym');
        $latest = PayrollRun::query()
            ->where('code', 'like', $prefix . '-%')
            ->orderByDesc('code')
            ->value('code');

        $lastSequence = $latest ? (int) substr((string) $latest, -3) : 0;

        return sprintf('%s-%03d', $prefix, $lastSequence + 1);
    }
}
