<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id', 'required_without:purchase_order_id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id', 'required_without:purchase_order_id'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'intent' => ['nullable', 'in:draft,submit'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['nullable', 'integer', 'exists:purchase_order_items,id'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:120'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = (array) $this->input('items', []);
            foreach ($items as $index => $item) {
                if (blank($item['purchase_order_item_id'] ?? null) && blank($item['product_id'] ?? null)) {
                    $validator->errors()->add("items.$index.product_id", 'Setiap item retur harus memiliki product_id atau purchase_order_item_id.');
                }
            }
        });
    }

    public function headerData(): array
    {
        return $this->safe()->only([
            'purchase_order_id',
            'supplier_id',
            'warehouse_id',
            'return_date',
            'notes',
        ]);
    }

    public function lineItems(): array
    {
        $validated = $this->validated();

        return $validated['items'] ?? [];
    }

    public function intent(): string
    {
        $validated = $this->validated();

        return $validated['intent'] ?? 'submit';
    }
}

