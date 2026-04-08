<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'reconciliation_date' => ['required', 'date'],
            'opening_balance' => ['required', 'numeric'],
            'counted_ending_balance' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

