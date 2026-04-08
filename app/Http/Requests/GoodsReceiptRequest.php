<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(fn ($item): array => [
                'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                'received_quantity' => $item['received_quantity'] ?? 0,
                'notes' => $item['notes'] ?? null,
            ])
            ->values()
            ->all();

        $this->merge([
            'notes' => $this->input('notes') ?: null,
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        return [
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'distinct', 'exists:purchase_order_items,id'],
            'items.*.received_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function headerData(): array
    {
        return $this->safe()->only([
            'received_at',
            'notes',
        ]);
    }

    public function lineItems(): array
    {
        $validated = $this->validated();

        return $validated['items'] ?? [];
    }
}
