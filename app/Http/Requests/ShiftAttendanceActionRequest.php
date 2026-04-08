<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShiftAttendanceActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'clock_at' => $this->input('clock_at') ?: null,
            'notes' => $this->input('notes') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'clock_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
