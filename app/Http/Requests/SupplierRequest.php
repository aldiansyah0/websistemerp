<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'contact_person' => $this->input('contact_person') ?: null,
            'email' => $this->input('email') ?: null,
            'phone' => $this->input('phone') ?: null,
            'city' => $this->input('city') ?: null,
            'address' => $this->input('address') ?: null,
            'lead_time_days' => $this->input('lead_time_days') ?: 0,
            'payment_term_days' => $this->input('payment_term_days') ?: 0,
            'fill_rate' => $this->input('fill_rate') ?: 0,
            'reject_rate' => $this->input('reject_rate') ?: 0,
            'rating' => $this->input('rating') ?: 0,
            'notes' => $this->input('notes') ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            'code' => ['required', 'string', 'max:40', Rule::unique('suppliers', 'code')->ignore($supplierId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($supplierId)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($supplierId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'fill_rate' => ['nullable', 'numeric', 'between:0,100'],
            'reject_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
