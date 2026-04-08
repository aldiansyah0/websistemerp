<?php

namespace App\Http\Requests;

use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'region' => $this->input('region') ?: null,
            'phone' => $this->input('phone') ?: null,
            'manager_name' => $this->input('manager_name') ?: null,
            'warehouse_id' => $this->input('warehouse_id') ?: null,
            'opening_date' => $this->input('opening_date') ?: null,
            'daily_sales_target' => $this->input('daily_sales_target') ?: 0,
            'service_level' => $this->input('service_level') ?: 0,
            'inventory_accuracy' => $this->input('inventory_accuracy') ?: 0,
            'is_fulfillment_hub' => $this->boolean('is_fulfillment_hub'),
            'address' => $this->input('address') ?: null,
        ]);
    }

    public function rules(): array
    {
        $outletId = $this->route('outlet')?->id;

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('outlets', 'code')->ignore($outletId)],
            'name' => ['required', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:60'],
            'city' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'opening_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(Outlet::statusOptions()))],
            'daily_sales_target' => ['nullable', 'numeric', 'min:0'],
            'service_level' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'inventory_accuracy' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_fulfillment_hub' => ['required', 'boolean'],
            'address' => ['nullable', 'string'],
        ];
    }
}
