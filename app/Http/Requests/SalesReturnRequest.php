<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'intent' => ['nullable', 'in:draft,submit'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_transaction_item_id' => ['nullable', 'integer', 'exists:sales_transaction_items,id'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:120'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = (array) $this->input('items', []);
            foreach ($items as $index => $item) {
                if (
                    blank($item['sales_transaction_item_id'] ?? null)
                    && blank($item['product_id'] ?? null)
                    && blank($item['product_variant_id'] ?? null)
                ) {
                    $validator->errors()->add("items.$index.product_id", 'Setiap item retur harus memiliki sales_transaction_item_id, product_id, atau product_variant_id.');
                }
            }
        });
    }

    public function headerData(): array
    {
        return $this->safe()->only(['return_date', 'notes']);
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
