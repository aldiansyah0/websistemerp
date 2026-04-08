<?php

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\PayrollRunItem;
use App\Models\SalesTransaction;
use App\Models\Shift;
use App\Services\PayrollCalculationService;
use App\Services\ShiftAttendanceService;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    $this->seed();
});

test('automatic payroll includes pos sales bonus and attendance deductions', function () {
    $employee = Employee::query()
        ->where('status', Employee::STATUS_ACTIVE)
        ->whereNotNull('location_id')
        ->whereNotNull('outlet_id')
        ->orderBy('id')
        ->firstOrFail();
    $employee->update([
        'sales_bonus_rate' => 2.5,
        'overtime_rate' => 30_000,
        'late_penalty_per_minute' => 2_000,
        'absence_penalty_amount' => 150_000,
    ]);

    $outlet = $employee->outlet;
    expect($outlet)->not->toBeNull();

    SalesTransaction::query()->create([
        'tenant_id' => $employee->tenant_id,
        'location_id' => $employee->location_id,
        'outlet_id' => $outlet->id,
        'cashier_employee_id' => $employee->id,
        'transaction_number' => 'UT-POS-20260501-' . $employee->id,
        'invoice_number' => 'UT-INV-20260501-' . $employee->id,
        'sold_at' => '2026-05-01 13:20:00',
        'invoice_date' => '2026-05-01',
        'due_date' => '2026-05-01',
        'gross_amount' => 500_000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'net_amount' => 500_000,
        'split_payment_count' => 1,
        'items_count' => 1,
        'status' => 'paid',
        'payment_status' => 'paid',
        'paid_amount' => 500_000,
        'balance_due' => 0,
        'customer_name' => 'Unit Test Customer',
    ]);

    AttendanceLog::query()->updateOrCreate(
        [
            'employee_id' => $employee->id,
            'shift_date' => '2026-05-01',
            'shift_name' => 'Morning',
        ],
        [
            'tenant_id' => $employee->tenant_id,
            'location_id' => $employee->location_id,
            'outlet_id' => $employee->outlet_id,
            'scheduled_start' => CarbonImmutable::parse('2026-05-01 08:00:00'),
            'scheduled_end' => CarbonImmutable::parse('2026-05-01 17:00:00'),
            'clock_in_at' => CarbonImmutable::parse('2026-05-01 08:10:00'),
            'clock_out_at' => CarbonImmutable::parse('2026-05-01 18:00:00'),
            'late_minutes' => 10,
            'overtime_minutes' => 60,
            'attendance_status' => 'late',
            'notes' => 'UT late shift',
        ]
    );
    AttendanceLog::query()->updateOrCreate(
        [
            'employee_id' => $employee->id,
            'shift_date' => '2026-05-02',
            'shift_name' => 'Morning',
        ],
        [
            'tenant_id' => $employee->tenant_id,
            'location_id' => $employee->location_id,
            'outlet_id' => $employee->outlet_id,
            'scheduled_start' => CarbonImmutable::parse('2026-05-02 08:00:00'),
            'scheduled_end' => CarbonImmutable::parse('2026-05-02 17:00:00'),
            'clock_in_at' => null,
            'clock_out_at' => null,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
            'attendance_status' => 'absent',
            'notes' => 'UT absent shift',
        ]
    );

    $run = app(PayrollCalculationService::class)->generate([
        'period_start' => '2026-05-01',
        'period_end' => '2026-05-31',
        'location_id' => $employee->location_id,
        'notes' => 'UT payroll generation',
    ]);

    $item = PayrollRunItem::query()
        ->where('payroll_run_id', $run->id)
        ->where('employee_id', $employee->id)
        ->firstOrFail();

    $expectedSalesBonus = 12_500.0;
    $expectedOvertime = 30_000.0;
    $expectedLateDeduction = 20_000.0;
    $expectedAbsenceDeduction = 150_000.0;
    $expectedAttendanceDeduction = $expectedLateDeduction + $expectedAbsenceDeduction;
    $expectedNet = (float) $employee->base_salary + $expectedSalesBonus + $expectedOvertime - $expectedAttendanceDeduction;

    expect(abs((float) $item->sales_bonus_amount - $expectedSalesBonus) < 0.01)->toBeTrue()
        ->and(abs((float) $item->attendance_deduction_amount - $expectedAttendanceDeduction) < 0.01)->toBeTrue()
        ->and(abs((float) $item->net_salary - $expectedNet) < 0.01)->toBeTrue();
});

test('shift attendance service syncs assignment to attendance log', function () {
    $employee = Employee::query()
        ->where('status', Employee::STATUS_ACTIVE)
        ->whereNotNull('location_id')
        ->orderBy('id')
        ->firstOrFail();
    $shift = Shift::query()->where('code', 'SHIFT-MORNING')->firstOrFail();

    $service = app(ShiftAttendanceService::class);
    $assignment = $service->assignShift([
        'employee_id' => $employee->id,
        'shift_id' => $shift->id,
        'location_id' => $employee->location_id,
        'shift_date' => '2026-05-03',
        'notes' => 'UT assignment',
    ]);

    $service->clockIn($assignment, '2026-05-03 08:12:00');
    $updated = $service->clockOut($assignment->fresh(), '2026-05-03 17:20:00');

    expect($updated->workflow_status)->toBe('checked_out')
        ->and($updated->attendance_status)->toBeIn(['present', 'late']);

    $exists = AttendanceLog::query()
        ->where('employee_id', $employee->id)
        ->whereDate('shift_date', '2026-05-03')
        ->where('shift_name', 'Morning Shift')
        ->exists();

    expect($exists)->toBeTrue();
});
