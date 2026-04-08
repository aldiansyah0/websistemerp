<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\Location;
use App\Models\Shift;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class ShiftAttendanceService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
    ) {
    }

    public function assignShift(array $attributes): EmployeeShiftAssignment
    {
        return DB::transaction(function () use ($attributes): EmployeeShiftAssignment {
            $employee = Employee::query()
                ->with(['location', 'outlet'])
                ->findOrFail((int) $attributes['employee_id']);
            $shift = Shift::query()->findOrFail((int) $attributes['shift_id']);
            $shiftDate = CarbonImmutable::parse($attributes['shift_date'])->startOfDay();

            [$scheduledStart, $scheduledEnd] = $this->resolveScheduleWindow($shift, $shiftDate);
            $locationId = $this->resolveLocationId($attributes['location_id'] ?? null, $employee, $shift);

            $assignment = EmployeeShiftAssignment::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shift->id,
                    'shift_date' => $shiftDate->toDateString(),
                ],
                [
                    'tenant_id' => $employee->tenant_id,
                    'location_id' => $locationId,
                    'scheduled_start' => $scheduledStart,
                    'scheduled_end' => $scheduledEnd,
                    'clock_in_at' => null,
                    'clock_out_at' => null,
                    'workflow_status' => EmployeeShiftAssignment::WORKFLOW_SCHEDULED,
                    'attendance_status' => EmployeeShiftAssignment::ATTENDANCE_OFF,
                    'late_minutes' => 0,
                    'overtime_minutes' => 0,
                    'notes' => $attributes['notes'] ?? null,
                ]
            );

            $this->syncAttendanceLog($assignment->fresh(['employee', 'shift', 'location']));
            $this->analyticsCacheService->invalidate();

            return $assignment->fresh(['employee', 'shift', 'location']);
        });
    }

    public function clockIn(EmployeeShiftAssignment $assignment, ?string $clockAt = null, ?string $notes = null): EmployeeShiftAssignment
    {
        return DB::transaction(function () use ($assignment, $clockAt, $notes): EmployeeShiftAssignment {
            $assignment->loadMissing(['employee', 'shift', 'location']);

            if (in_array($assignment->workflow_status, [EmployeeShiftAssignment::WORKFLOW_CANCELLED, EmployeeShiftAssignment::WORKFLOW_CLOSED], true)) {
                throw new DomainException('Shift ini sudah ditutup dan tidak bisa clock in.');
            }

            $clockInAt = $clockAt !== null
                ? CarbonImmutable::parse($clockAt)
                : CarbonImmutable::now('Asia/Jakarta');
            $scheduledStart = CarbonImmutable::parse($assignment->scheduled_start);
            $minutesDiff = max($scheduledStart->diffInMinutes($clockInAt, false), 0);
            $graceMinutes = max((int) ($assignment->shift?->grace_minutes ?? 0), 0);
            $lateMinutes = max($minutesDiff - $graceMinutes, 0);

            $assignment->clock_in_at = $clockInAt;
            $assignment->late_minutes = $lateMinutes;
            $assignment->attendance_status = $lateMinutes > 0
                ? EmployeeShiftAssignment::ATTENDANCE_LATE
                : EmployeeShiftAssignment::ATTENDANCE_PRESENT;
            $assignment->workflow_status = EmployeeShiftAssignment::WORKFLOW_CHECKED_IN;
            if ($notes !== null) {
                $assignment->notes = $notes;
            }
            $assignment->save();

            $this->syncAttendanceLog($assignment->fresh(['employee', 'shift', 'location']));
            $this->analyticsCacheService->invalidate();

            return $assignment->fresh(['employee', 'shift', 'location']);
        });
    }

    public function clockOut(EmployeeShiftAssignment $assignment, ?string $clockAt = null, ?string $notes = null): EmployeeShiftAssignment
    {
        return DB::transaction(function () use ($assignment, $clockAt, $notes): EmployeeShiftAssignment {
            $assignment->loadMissing(['employee', 'shift', 'location']);

            if ($assignment->clock_in_at === null) {
                throw new DomainException('Clock out tidak bisa dilakukan sebelum clock in.');
            }

            if ($assignment->workflow_status === EmployeeShiftAssignment::WORKFLOW_CANCELLED) {
                throw new DomainException('Shift ini sudah dibatalkan.');
            }

            $clockOutAt = $clockAt !== null
                ? CarbonImmutable::parse($clockAt)
                : CarbonImmutable::now('Asia/Jakarta');
            $clockInAt = CarbonImmutable::parse($assignment->clock_in_at);
            if ($clockOutAt->lessThan($clockInAt)) {
                throw new DomainException('Jam clock out tidak boleh lebih kecil dari jam clock in.');
            }

            $scheduledEnd = CarbonImmutable::parse($assignment->scheduled_end);
            $overtimeMinutes = max($scheduledEnd->diffInMinutes($clockOutAt, false), 0);
            $maxOvertime = max((int) ($assignment->shift?->max_overtime_minutes ?? 0), 0);
            if ($maxOvertime > 0) {
                $overtimeMinutes = min($overtimeMinutes, $maxOvertime);
            }

            $assignment->clock_out_at = $clockOutAt;
            $assignment->overtime_minutes = $overtimeMinutes;
            $assignment->workflow_status = EmployeeShiftAssignment::WORKFLOW_CHECKED_OUT;
            if ($notes !== null) {
                $assignment->notes = $notes;
            }
            $assignment->save();

            $this->syncAttendanceLog($assignment->fresh(['employee', 'shift', 'location']));
            $this->analyticsCacheService->invalidate();

            return $assignment->fresh(['employee', 'shift', 'location']);
        });
    }

    public function markAbsent(EmployeeShiftAssignment $assignment, ?string $notes = null): EmployeeShiftAssignment
    {
        return DB::transaction(function () use ($assignment, $notes): EmployeeShiftAssignment {
            $assignment->loadMissing(['employee', 'shift', 'location']);

            if ($assignment->workflow_status === EmployeeShiftAssignment::WORKFLOW_CHECKED_OUT) {
                throw new DomainException('Shift ini sudah clock out dan tidak bisa ditandai absent.');
            }

            $assignment->clock_in_at = null;
            $assignment->clock_out_at = null;
            $assignment->late_minutes = 0;
            $assignment->overtime_minutes = 0;
            $assignment->attendance_status = EmployeeShiftAssignment::ATTENDANCE_ABSENT;
            $assignment->workflow_status = EmployeeShiftAssignment::WORKFLOW_CLOSED;
            if ($notes !== null) {
                $assignment->notes = $notes;
            }
            $assignment->save();

            $this->syncAttendanceLog($assignment->fresh(['employee', 'shift', 'location']));
            $this->analyticsCacheService->invalidate();

            return $assignment->fresh(['employee', 'shift', 'location']);
        });
    }

    private function syncAttendanceLog(EmployeeShiftAssignment $assignment): void
    {
        $employee = $assignment->employee;
        if (! $employee) {
            return;
        }

        $attendanceStatus = $assignment->attendance_status;
        if (! in_array($attendanceStatus, ['present', 'late', 'absent', 'leave', 'off'], true)) {
            $attendanceStatus = 'off';
        }

        $shiftDate = $assignment->shift_date?->toDateString();
        if ($shiftDate === null) {
            return;
        }

        $basePayload = [
            'tenant_id' => $assignment->tenant_id ?? $employee->tenant_id,
            'location_id' => $assignment->location_id ?? $employee->location_id,
            'outlet_id' => $this->resolveOutletId($employee, $assignment->location_id),
            'scheduled_start' => $assignment->scheduled_start,
            'scheduled_end' => $assignment->scheduled_end,
            'clock_in_at' => $assignment->clock_in_at,
            'clock_out_at' => $assignment->clock_out_at,
            'late_minutes' => $assignment->late_minutes,
            'overtime_minutes' => $assignment->overtime_minutes,
            'attendance_status' => $attendanceStatus,
            'notes' => $assignment->notes,
        ];

        $existingLog = AttendanceLog::query()
            ->where('employee_id', $employee->id)
            ->whereDate('shift_date', $shiftDate)
            ->where('shift_name', $assignment->shift?->name ?? 'Shift')
            ->first();

        if ($existingLog !== null) {
            $existingLog->update($basePayload);

            return;
        }

        AttendanceLog::query()->create(array_merge($basePayload, [
            'employee_id' => $employee->id,
            'shift_date' => $shiftDate,
            'shift_name' => $assignment->shift?->name ?? 'Shift',
        ]));
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function resolveScheduleWindow(Shift $shift, CarbonImmutable $shiftDate): array
    {
        $scheduledStart = $shiftDate->setTimeFromTimeString((string) $shift->start_time);
        $scheduledEnd = $shiftDate->setTimeFromTimeString((string) $shift->end_time);

        if ($shift->is_overnight || $scheduledEnd->lessThanOrEqualTo($scheduledStart)) {
            $scheduledEnd = $scheduledEnd->addDay();
        }

        return [$scheduledStart, $scheduledEnd];
    }

    private function resolveLocationId(?int $selectedLocationId, Employee $employee, Shift $shift): ?int
    {
        if (filled($selectedLocationId)) {
            return (int) $selectedLocationId;
        }

        if (filled($employee->location_id)) {
            return (int) $employee->location_id;
        }

        if (filled($shift->location_id)) {
            return (int) $shift->location_id;
        }

        return null;
    }

    private function resolveOutletId(Employee $employee, ?int $locationId): ?int
    {
        if ($locationId !== null) {
            $location = Location::query()->find($locationId);

            if ($location?->type === Location::TYPE_OUTLET) {
                return $location->legacy_outlet_id;
            }
        }

        return $employee->outlet_id;
    }
}
