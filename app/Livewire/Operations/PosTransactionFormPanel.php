<?php

namespace App\Livewire\Operations;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Outlet;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Workflows\PosTransactionWorkflow;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PosTransactionFormPanel extends Component
{
    public ?int $outlet_id = null;
    public ?int $cashier_employee_id = null;
    public ?int $customer_id = null;
    public string $customer_name = '';
    public string $sold_at = '';
    public string $due_date = '';
    public string $notes = '';

    public string $barcode = '';
    public string $scanError = '';

    /**
     * @var array<int, array{product_id: string|int|null, quantity: float|int|string, unit_price: float|int|string, discount_amount: float|int|string, notes: string|null}>
     */
    public array $items = [];

    /**
     * @var array<int, array{payment_method_id: string|int|null, amount: float|int|string, reference_number: string|null, approval_code: string|null}>
     */
    public array $payments = [];

    public function mount(): void
    {
        $this->sold_at = now('Asia/Jakarta')->format('Y-m-d\TH:i');
        $this->due_date = now('Asia/Jakarta')->toDateString();
        $this->items = [$this->blankItem()];
        $this->payments = [$this->blankPayment()];
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items[] = $this->blankItem();
        }
    }

    public function addPayment(): void
    {
        $this->payments[] = $this->blankPayment();
    }

    public function removePayment(int $index): void
    {
        if (! isset($this->payments[$index])) {
            return;
        }

        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);

        if ($this->payments === []) {
            $this->payments[] = $this->blankPayment();
        }
    }

    public function scanBarcode(): void
    {
        $this->scanError = '';
        $token = strtoupper(trim($this->barcode));

        if ($token === '') {
            $this->scanError = 'Barcode tidak boleh kosong.';

            return;
        }

        $product = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where(function ($query) use ($token): void {
                $query->whereRaw('UPPER(barcode) = ?', [$token])
                    ->orWhereRaw('UPPER(sku) = ?', [$token]);
            })
            ->first();

        if (! $product) {
            $this->scanError = 'Barcode / SKU tidak ditemukan.';
            $this->barcode = '';

            return;
        }

        $existingIndex = collect($this->items)->search(
            fn (array $item): bool => (string) ($item['product_id'] ?? '') === (string) $product->id
        );

        if ($existingIndex !== false) {
            $this->items[$existingIndex]['quantity'] = (float) ($this->items[$existingIndex]['quantity'] ?? 0) + 1;
        } elseif (count($this->items) === 1 && blank($this->items[0]['product_id'] ?? null)) {
            $this->items[0] = [
                'product_id' => (string) $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->selling_price,
                'discount_amount' => 0,
                'notes' => '',
            ];
        } else {
            $this->items[] = [
                'product_id' => (string) $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->selling_price,
                'discount_amount' => 0,
                'notes' => '',
            ];
        }

        $this->barcode = '';
    }

    public function syncProduct(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $productId = $this->items[$index]['product_id'] ?? null;

        if (! filled($productId)) {
            return;
        }

        $product = Product::query()->find((int) $productId);
        if (! $product) {
            return;
        }

        $currentPrice = (float) ($this->items[$index]['unit_price'] ?? 0);
        if ($currentPrice <= 0) {
            $this->items[$index]['unit_price'] = (float) $product->selling_price;
        }
    }

    public function save(PosTransactionWorkflow $workflow)
    {
        $this->scanError = '';

        $this->items = collect($this->items)
            ->map(fn (array $item): array => [
                'product_id' => $item['product_id'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'notes' => $item['notes'] ?? null,
            ])
            ->filter(fn (array $item): bool => filled($item['product_id']) || filled($item['quantity']) || filled($item['unit_price']))
            ->values()
            ->all();

        $this->payments = collect($this->payments)
            ->map(fn (array $payment): array => [
                'payment_method_id' => $payment['payment_method_id'] ?? null,
                'amount' => $payment['amount'] ?? null,
                'reference_number' => $payment['reference_number'] ?? null,
                'approval_code' => $payment['approval_code'] ?? null,
            ])
            ->filter(fn (array $payment): bool => filled($payment['payment_method_id']) || filled($payment['amount']))
            ->values()
            ->all();

        if ($this->items === []) {
            $this->items = [$this->blankItem()];
        }

        if ($this->payments === []) {
            $this->payments = [$this->blankPayment()];
        }

        $validated = $this->validate();

        $soldDate = CarbonImmutable::parse((string) $validated['sold_at'])->toDateString();
        if (filled($validated['due_date'] ?? null) && (string) $validated['due_date'] < $soldDate) {
            $this->addError('due_date', 'Tanggal jatuh tempo tidak boleh sebelum tanggal transaksi.');

            return null;
        }

        $netAmount = collect($validated['items'])->sum(function (array $item): float {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $discount = (float) ($item['discount_amount'] ?? 0);

            return max(($quantity * $unitPrice) - $discount, 0);
        });
        $paymentTotal = collect($validated['payments'])->sum(fn (array $payment): float => (float) $payment['amount']);

        if (abs($netAmount - $paymentTotal) > 0.01) {
            $this->addError('payments', 'Total bayar harus sama dengan total belanja.');

            return null;
        }

        $header = [
            'outlet_id' => (int) $validated['outlet_id'],
            'cashier_employee_id' => $validated['cashier_employee_id'] ? (int) $validated['cashier_employee_id'] : null,
            'customer_id' => $validated['customer_id'] ? (int) $validated['customer_id'] : null,
            'customer_name' => filled($validated['customer_name']) ? trim((string) $validated['customer_name']) : null,
            'sold_at' => $validated['sold_at'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => filled($validated['notes']) ? trim((string) $validated['notes']) : null,
        ];

        $items = collect($validated['items'])->map(fn (array $item): array => [
            'product_id' => (int) $item['product_id'],
            'quantity' => (float) $item['quantity'],
            'unit_price' => (float) $item['unit_price'],
            'discount_amount' => (float) ($item['discount_amount'] ?? 0),
            'notes' => filled($item['notes'] ?? null) ? trim((string) $item['notes']) : null,
        ])->all();

        $payments = collect($validated['payments'])->map(fn (array $payment): array => [
            'payment_method_id' => (int) $payment['payment_method_id'],
            'amount' => (float) $payment['amount'],
            'reference_number' => filled($payment['reference_number'] ?? null) ? trim((string) $payment['reference_number']) : null,
            'approval_code' => filled($payment['approval_code'] ?? null) ? trim((string) $payment['approval_code']) : null,
        ])->all();

        try {
            $transaction = $workflow->store($header, $items, $payments);
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());

            return null;
        }

        session()->flash('success', 'Transaksi POS ' . $transaction->transaction_number . ' berhasil diposting.');

        return redirect()->route('pos-transactions');
    }

    protected function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'cashier_employee_id' => ['nullable', 'exists:employees,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'sold_at' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
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

    public function render(): View
    {
        return view('livewire.operations.pos-transaction-form-panel', [
            'title' => 'Buat Transaksi POS',
            'pageTitle' => 'Buat Transaksi POS',
            'pageEyebrow' => 'Sales & POS',
            'pageDescription' => 'Checkout retail barcode-centric dengan split payment real-time dan posting stok atomik.',
            'backUrl' => route('pos-transactions'),
            'generatedAt' => now('Asia/Jakarta')->translatedFormat('d F Y, H:i'),
            'outlets' => Outlet::query()->where('status', Outlet::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']),
            'cashiers' => Employee::query()->where('status', Employee::STATUS_ACTIVE)->orderBy('full_name')->get(['id', 'full_name']),
            'customers' => Customer::query()->where('status', Customer::STATUS_ACTIVE)->orderBy('name')->get(['id', 'code', 'name']),
            'paymentMethods' => PaymentMethod::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'sku', 'barcode', 'name', 'selling_price']),
            'netTotal' => $this->netTotal(),
            'paymentTotal' => $this->paymentTotal(),
        ]);
    }

    private function netTotal(): float
    {
        return collect($this->items)->sum(function (array $item): float {
            $quantity = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);

            return max(($quantity * $price) - $discount, 0);
        });
    }

    private function paymentTotal(): float
    {
        return collect($this->payments)->sum(fn (array $payment): float => (float) ($payment['amount'] ?? 0));
    }

    /**
     * @return array{product_id: string, quantity: int, unit_price: int, discount_amount: int, notes: string}
     */
    private function blankItem(): array
    {
        return [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_amount' => 0,
            'notes' => '',
        ];
    }

    /**
     * @return array{payment_method_id: string, amount: string, reference_number: string, approval_code: string}
     */
    private function blankPayment(): array
    {
        return [
            'payment_method_id' => '',
            'amount' => '',
            'reference_number' => '',
            'approval_code' => '',
        ];
    }
}
