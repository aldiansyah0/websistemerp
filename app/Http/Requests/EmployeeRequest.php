<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_id' => $this->input('location_id') ?: null,
            'outlet_id' => $this->input('outlet_id') ?: null,
            'email' => $this->input('email') ?: null,
            'phone' => $this->input('phone') ?: null,
            'emergency_contact' => $this->input('emergency_contact') ?: null,
            'notes' => $this->input('notes') ?: null,
            'sales_bonus_rate' => $this->input('sales_bonus_rate') ?: 0,
            'overtime_rate' => $this->input('overtime_rate') ?: 0,
            'late_penalty_per_minute' => $this->input('late_penalty_per_minute') ?: 0,
            'absence_penalty_amount' => $this->input('absence_penalty_amount') ?: 0,
        ]);
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'location_id' => ['nullable', 'exists:locations,id'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'employee_code' => ['required', 'string', 'max:30', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employeeId)],
            'phone' => ['nullable', 'string', 'max:40'],
            'department' => ['required', 'string', 'max:80'],
            'position_title' => ['required', 'string', 'max:120'],
            'employment_type' => ['required', Rule::in(array_keys(Employee::employmentTypeOptions()))],
            'join_date' => ['required', 'date'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'sales_bonus_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'late_penalty_per_minute' => ['nullable', 'numeric', 'min:0'],
            'absence_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(Employee::statusOptions()))],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
