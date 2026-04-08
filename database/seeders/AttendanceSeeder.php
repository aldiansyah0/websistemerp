<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::query()->with('outlet')->get()->keyBy('employee_code');
        $shiftDate = CarbonImmutable::parse('2026-04-08');

        $logs = [
            ['employee' => 'EMP-001', 'shift' => 'Morning', 'start' => '08:00', 'end' => '17:00', 'status' => 'present', 'late' => 0, 'overtime' => 20],
            ['employee' => 'EMP-002', 'shift' => 'Morning', 'start' => '08:00', 'end' => '17:00', 'status' => 'late', 'late' => 12, 'overtime' => 0],
            ['employee' => 'EMP-003', 'shift' => 'Morning', 'start' => '09:00', 'end' => '18:00', 'status' => 'present', 'late' => 0, 'overtime' => 10],
            ['employee' => 'EMP-004', 'shift' => 'Afternoon', 'start' => '13:00', 'end' => '21:00', 'status' => 'present', 'late' => 0, 'overtime' => 0],
            ['employee' => 'EMP-005', 'shift' => 'Morning', 'start' => '08:00', 'end' => '17:00', 'status' => 'present', 'late' => 0, 'overtime' => 15],
            ['employee' => 'EMP-006', 'shift' => 'Afternoon', 'start' => '12:00', 'end' => '21:00', 'status' => 'late', 'late' => 8, 'overtime' => 30],
            ['employee' => 'EMP-007', 'shift' => 'Morning', 'start' => '09:00', 'end' => '18:00', 'status' => 'present', 'late' => 0, 'overtime' => 0],
            ['employee' => 'EMP-008', 'shift' => 'Morning', 'start' => '09:00', 'end' => '18:00', 'status' => 'leave', 'late' => 0, 'overtime' => 0],
            ['employee' => 'EMP-009', 'shift' => 'Office', 'start' => '08:30', 'end' => '17:30', 'status' => 'present', 'late' => 0, 'overtime' => 0],
            ['employee' => 'EMP-010', 'shift' => 'Office', 'start' => '08:30', 'end' => '17:30', 'status' => 'present', 'late' => 0, 'overtime' => 45],
            ['employee' => 'EMP-011', 'shift' => 'Warehouse', 'start' => '07:30', 'end' => '16:30', 'status' => 'present', 'late' => 0, 'overtime' => 55],
        ];

        foreach ($logs as $log) {
            $employee = $employees[$log['employee']] ?? null;

            if (! $employee) {
                continue;
            }

            $scheduledStart = $shiftDate->setTimeFromTimeString($log['start']);
            $scheduledEnd = $shiftDate->setTimeFromTimeString($log['end']);
            $clockIn = in_array($log['status'], ['leave', 'absent', 'off'], true) ? null : $scheduledStart->addMinutes($log['late']);
            $clockOut = $clockIn ? $scheduledEnd->addMinutes($log['overtime']) : null;

            AttendanceLog::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'shift_date' => $shiftDate->toDateString(),
                    'shift_name' => $log['shift'],
                ],
                [
                    'outlet_id' => $employee->outlet_id,
                    'location_id' => $employee->location_id,
                    'scheduled_start' => $scheduledStart,
                    'scheduled_end' => $scheduledEnd,
                    'clock_in_at' => $clockIn,
                    'clock_out_at' => $clockOut,
                    'late_minutes' => $log['late'],
                    'overtime_minutes' => $log['overtime'],
                    'attendance_status' => $log['status'],
                    'notes' => 'Seed attendance ' . strtolower($log['shift']),
                ]
            );
        }
    }
}
