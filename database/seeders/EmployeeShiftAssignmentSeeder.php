<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\EmployeeShiftAssignment;
use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class EmployeeShiftAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $shiftByCode = Shift::query()->pluck('id', 'code');
        $shiftMap = [
            'morning' => $shiftByCode['SHIFT-MORNING'] ?? null,
            'afternoon' => $shiftByCode['SHIFT-AFTERNOON'] ?? null,
            'office' => $shiftByCode['SHIFT-OFFICE'] ?? null,
            'warehouse' => $shiftByCode['SHIFT-WAREHOUSE'] ?? null,
        ];

        AttendanceLog::query()
            ->with('employee')
            ->orderBy('shift_date')
            ->get()
            ->each(function (AttendanceLog $log) use ($shiftMap): void {
                $shiftKey = strtolower(trim((string) $log->shift_name));
                $shiftId = $shiftMap[$shiftKey] ?? null;
                if ($shiftId === null) {
                    return;
                }

                $scheduledStart = $log->scheduled_start
                    ? CarbonImmutable::parse($log->scheduled_start)
                    : CarbonImmutable::parse($log->shift_date->format('Y-m-d') . ' 08:00:00');
                $scheduledEnd = $log->scheduled_end
                    ? CarbonImmutable::parse($log->scheduled_end)
                    : CarbonImmutable::parse($log->shift_date->format('Y-m-d') . ' 17:00:00');
                $workflowStatus = match (true) {
                    $log->clock_out_at !== null => EmployeeShiftAssignment::WORKFLOW_CHECKED_OUT,
                    $log->clock_in_at !== null => EmployeeShiftAssignment::WORKFLOW_CHECKED_IN,
                    in_array($log->attendance_status, ['absent', 'leave'], true) => EmployeeShiftAssignment::WORKFLOW_CLOSED,
                    default => EmployeeShiftAssignment::WORKFLOW_SCHEDULED,
                };

                EmployeeShiftAssignment::query()->updateOrCreate(
                    [
                        'employee_id' => $log->employee_id,
                        'shift_id' => $shiftId,
                        'shift_date' => $log->shift_date->toDateString(),
                    ],
                    [
                        'tenant_id' => $log->employee?->tenant_id,
                        'location_id' => $log->location_id ?? $log->employee?->location_id,
                        'scheduled_start' => $scheduledStart,
                        'scheduled_end' => $scheduledEnd,
                        'clock_in_at' => $log->clock_in_at,
                        'clock_out_at' => $log->clock_out_at,
                        'workflow_status' => $workflowStatus,
                        'attendance_status' => $log->attendance_status,
                        'late_minutes' => (int) $log->late_minutes,
                        'overtime_minutes' => (int) $log->overtime_minutes,
                        'notes' => $log->notes,
                    ]
                );
            });
    }
}
