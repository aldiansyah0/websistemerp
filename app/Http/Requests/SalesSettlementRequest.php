<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_date' => $this->input('payment_date') ?: now()->format('Y-m-d\TH:i'),
            'reference_number' => $this->input('reference_number') ?: null,
            'approval_code' => $this->input('approval_code') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'approval_code' => ['nullable', 'string', 'max:100'],
        ];
    }
}
