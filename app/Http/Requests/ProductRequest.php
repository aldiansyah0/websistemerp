<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'primary_supplier_id' => $this->input('primary_supplier_id') ?: null,
            'barcode' => $this->input('barcode') ?: null,
            'description' => $this->input('description') ?: null,
            'daily_run_rate' => $this->input('daily_run_rate') ?: 0,
            'reorder_level' => $this->input('reorder_level') ?: 0,
            'reorder_quantity' => $this->input('reorder_quantity') ?: 0,
            'shelf_life_days' => $this->input('shelf_life_days') ?: null,
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'primary_supplier_id' => ['nullable', 'exists:suppliers,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')->ignore($productId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_of_measure' => ['required', Rule::in(['pcs', 'box', 'btl', 'cup', 'pack'])],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'gte:cost_price'],
            'daily_run_rate' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'shelf_life_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys(Product::statusOptions()))],
            'is_featured' => ['required', 'boolean'],
        ];
    }
}
