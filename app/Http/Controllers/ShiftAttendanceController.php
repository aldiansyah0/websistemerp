<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShiftAssignmentRequest;
use App\Http\Requests\ShiftAttendanceActionRequest;
use App\Models\EmployeeShiftAssignment;
use App\Services\ShiftAttendanceService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ShiftAttendanceController extends Controller
{
    public function assign(ShiftAssignmentRequest $request, ShiftAttendanceService $shiftAttendanceService): RedirectResponse
    {
        try {
            $shiftAttendanceService->assignShift($request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('shift-attendance')->with('error', $exception->getMessage());
        }

        return redirect()->route('shift-attendance')->with('success', 'Shift berhasil dijadwalkan.');
    }

    public function clockIn(ShiftAttendanceActionRequest $request, EmployeeShiftAssignment $assignment, ShiftAttendanceService $shiftAttendanceService): RedirectResponse
    {
        try {
            $shiftAttendanceService->clockIn(
                assignment: $assignment,
                clockAt: $request->validated('clock_at'),
                notes: $request->validated('notes'),
            );
        } catch (DomainException $exception) {
            return redirect()->route('shift-attendance')->with('error', $exception->getMessage());
        }

        return redirect()->route('shift-attendance')->with('success', 'Clock in berhasil disimpan.');
    }

    public function clockOut(ShiftAttendanceActionRequest $request, EmployeeShiftAssignment $assignment, ShiftAttendanceService $shiftAttendanceService): RedirectResponse
    {
        try {
            $shiftAttendanceService->clockOut(
                assignment: $assignment,
                clockAt: $request->validated('clock_at'),
                notes: $request->validated('notes'),
            );
        } catch (DomainException $exception) {
            return redirect()->route('shift-attendance')->with('error', $exception->getMessage());
        }

        return redirect()->route('shift-attendance')->with('success', 'Clock out berhasil disimpan.');
    }

    public function markAbsent(ShiftAttendanceActionRequest $request, EmployeeShiftAssignment $assignment, ShiftAttendanceService $shiftAttendanceService): RedirectResponse
    {
        try {
            $shiftAttendanceService->markAbsent(
                assignment: $assignment,
                notes: $request->validated('notes'),
            );
        } catch (DomainException $exception) {
            return redirect()->route('shift-attendance')->with('error', $exception->getMessage());
        }

        return redirect()->route('shift-attendance')->with('success', 'Shift ditandai absent.');
    }
}
