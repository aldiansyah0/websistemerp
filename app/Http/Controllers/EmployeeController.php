<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\RetailOperationsService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function create(RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.employee-form', $retailOperationsService->employeeFormData());
    }

    public function store(EmployeeRequest $request, EmployeeService $employeeService): RedirectResponse
    {
        try {
            $employeeService->create($request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('employee-management')->with('error', $exception->getMessage());
        }

        return redirect()->route('employee-management')->with('success', 'Karyawan baru berhasil ditambahkan ke modul HR.');
    }

    public function edit(Employee $employee, RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.employee-form', $retailOperationsService->employeeFormData($employee));
    }

    public function update(EmployeeRequest $request, Employee $employee, EmployeeService $employeeService): RedirectResponse
    {
        try {
            $employeeService->update($employee, $request->validated());
        } catch (DomainException $exception) {
            return redirect()->route('employee-management')->with('error', $exception->getMessage());
        }

        return redirect()->route('employee-management')->with('success', 'Data karyawan berhasil diperbarui.');
    }
}
