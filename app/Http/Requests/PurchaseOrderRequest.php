<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(fn ($item): array => [
                'product_id' => $item['product_id'] ?? null,
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'ordered_quantity' => $item['ordered_quantity'] ?? null,
                'unit_cost' => $item['unit_cost'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'notes' => $item['notes'] ?? null,
            ])
            ->filter(fn (array $item): bool => filled($item['product_id']) || filled($item['product_variant_id']) || filled($item['ordered_quantity']) || filled($item['unit_cost']) || filled($item['notes']))
            ->values()
            ->all();

        $this->merge([
            'expected_date' => $this->input('expected_date') ?: null,
            'terms' => $this->input('terms') ?: null,
            'notes' => $this->input('notes') ?: null,
            'intent' => $this->input('intent', 'draft'),
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'intent' => ['nullable', Rule::in(['draft', 'submit'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
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

    public function headerData(): array
    {
        return $this->safe()->only([
            'supplier_id',
            'warehouse_id',
            'order_date',
            'expected_date',
            'terms',
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

        return $validated['intent'] ?? 'draft';
    }
}
