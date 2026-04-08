<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollGenerateRequest extends FormRequest
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
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
