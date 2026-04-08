<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(fn ($item): array => [
                'stock_transfer_item_id' => $item['stock_transfer_item_id'] ?? null,
                'received_quantity' => $item['received_quantity'] ?? 0,
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
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_transfer_item_id' => ['required', 'distinct', 'exists:stock_transfer_items,id'],
            'items.*.received_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function lineItems(): array
    {
        $validated = $this->validated();

        return $validated['items'] ?? [];
    }
}
