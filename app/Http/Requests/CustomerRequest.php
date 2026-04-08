<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->input('email') ?: null,
            'phone' => $this->input('phone') ?: null,
            'city' => $this->input('city') ?: null,
            'address' => $this->input('address') ?: null,
            'notes' => $this->input('notes') ?: null,
        ]);
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('customers', 'code')->ignore($customerId)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'segment' => ['required', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'payment_term_days' => ['required', 'integer', 'min:0', 'max:365'],
            'status' => ['required', Rule::in(array_keys(Customer::statusOptions()))],
            'notes' => ['nullable', 'string'],
        ];
    }
}
