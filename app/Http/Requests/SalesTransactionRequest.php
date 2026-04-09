<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesTransactionRequest extends FormRequest
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
                'quantity' => $item['quantity'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'notes' => $item['notes'] ?? null,
            ])
            ->filter(fn (array $item): bool => filled($item['product_id']) || filled($item['product_variant_id']) || filled($item['quantity']) || filled($item['unit_price']))
            ->values()
            ->all();

        $payments = collect($this->input('payments', []))
            ->map(fn ($payment): array => [
                'payment_method_id' => $payment['payment_method_id'] ?? null,
                'amount' => $payment['amount'] ?? null,
                'reference_number' => $payment['reference_number'] ?? null,
                'approval_code' => $payment['approval_code'] ?? null,
            ])
            ->filter(fn (array $payment): bool => filled($payment['payment_method_id']) || filled($payment['amount']))
            ->values()
            ->all();

        $this->merge([
            'cashier_employee_id' => $this->input('cashier_employee_id') ?: null,
            'customer_id' => $this->input('customer_id') ?: null,
            'customer_name' => $this->input('customer_name') ?: null,
            'invoice_date' => $this->input('invoice_date') ?: null,
            'due_date' => $this->input('due_date') ?: null,
            'notes' => $this->input('notes') ?: null,
            'items' => $items,
            'payments' => $payments,
        ]);
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'cashier_employee_id' => ['nullable', 'exists:employees,id'],
            'sold_at' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:sold_at'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payments.*.amount' => ['required', 'numeric', 'gt:0'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:255'],
            'payments.*.approval_code' => ['nullable', 'string', 'max:255'],
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

            $items = collect($this->input('items', []));
            $payments = collect($this->input('payments', []));

            $netAmount = $items->sum(function (array $item): float {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $discount = (float) ($item['discount_amount'] ?? 0);

                return max(($quantity * $unitPrice) - $discount, 0);
            });
            $paymentTotal = $payments->sum(fn (array $payment): float => (float) ($payment['amount'] ?? 0));

            if (abs($netAmount - $paymentTotal) > 0.01) {
                $validator->errors()->add('payments', 'Total bayar harus sama dengan total belanja.');
            }
        });
    }

    public function headerData(): array
    {
        return $this->safe()->only([
            'outlet_id',
            'cashier_employee_id',
            'customer_id',
            'sold_at',
            'customer_name',
            'invoice_date',
            'due_date',
            'notes',
        ]);
    }

    public function lineItems(): array
    {
        $validated = $this->validated();

        return $validated['items'] ?? [];
    }

    public function paymentLines(): array
    {
        $validated = $this->validated();

        return $validated['payments'] ?? [];
    }
}
