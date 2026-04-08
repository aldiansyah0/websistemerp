<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_id' => $this->input('location_id') ?: null,
            'notes' => $this->input('notes') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'shift_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
