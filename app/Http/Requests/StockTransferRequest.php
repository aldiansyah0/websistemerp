<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
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
                'requested_quantity' => $item['requested_quantity'] ?? null,
                'notes' => $item['notes'] ?? null,
            ])
            ->filter(fn (array $item): bool => filled($item['product_id']) || filled($item['product_variant_id']) || filled($item['requested_quantity']) || filled($item['notes']))
            ->values()
            ->all();

        $this->merge([
            'expected_receipt_date' => $this->input('expected_receipt_date') ?: null,
            'notes' => $this->input('notes') ?: null,
            'intent' => $this->input('intent', 'draft'),
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        return [
            'source_warehouse_id' => ['required', 'exists:warehouses,id', 'different:destination_warehouse_id'],
            'destination_warehouse_id' => ['required', 'exists:warehouses,id'],
            'request_date' => ['required', 'date'],
            'expected_receipt_date' => ['nullable', 'date', 'after_or_equal:request_date'],
            'notes' => ['nullable', 'string'],
            'intent' => ['nullable', Rule::in(['draft', 'submit'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.requested_quantity' => ['required', 'numeric', 'gt:0'],
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
            'source_warehouse_id',
            'destination_warehouse_id',
            'request_date',
            'expected_receipt_date',
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
