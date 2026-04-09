<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOpnameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'intent' => ['nullable', 'in:draft,submit'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.system_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.physical_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function headerData(): array
    {
        return $this->safe()->only(['warehouse_id', 'opname_date', 'notes']);
    }

    public function lineItems(): array
    {
        $validated = $this->validated();

        return $validated['items'] ?? [];
    }

    public function intent(): string
    {
        $validated = $this->validated();

        return $validated['intent'] ?? 'draft';
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ((array) $this->input('items', []) as $index => $item) {
                if (blank($item['product_id'] ?? null) && blank($item['product_variant_id'] ?? null)) {
                    $validator->errors()->add("items.$index.product_id", 'Setiap item wajib memiliki product_id atau product_variant_id.');
                }
            }
        });
    }
}
