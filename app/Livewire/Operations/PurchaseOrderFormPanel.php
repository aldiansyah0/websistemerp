<?php

namespace App\Livewire\Operations;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Workflows\PurchaseOrderWorkflow;
use DomainException;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PurchaseOrderFormPanel extends Component
{
    public ?int $purchaseOrderId = null;

    public ?int $supplier_id = null;
    public ?int $warehouse_id = null;
    public string $order_date = '';
    public ?string $expected_date = null;
    public string $terms = '';
    public string $notes = '';

    /**
     * @var array<int, array{product_id: string|int|null, ordered_quantity: float|int|string, unit_cost: float|int|string, discount_amount: float|int|string, notes: string|null}>
     */
    public array $items = [];

    public bool $canEdit = true;

    public function mount(?int $purchaseOrderId = null): void
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->order_date = now('Asia/Jakarta')->toDateString();
        $this->items = [$this->blankItem()];

        if ($purchaseOrderId === null) {
            return;
        }

        $purchaseOrder = PurchaseOrder::query()->with('items')->findOrFail($purchaseOrderId);
        $this->canEdit = $purchaseOrder->canBeEdited();

        $this->supplier_id = $purchaseOrder->supplier_id;
        $this->warehouse_id = $purchaseOrder->warehouse_id;
        $this->order_date = (string) $purchaseOrder->order_date?->toDateString();
        $this->expected_date = $purchaseOrder->expected_date?->toDateString();
        $this->terms = (string) ($purchaseOrder->terms ?? '');
        $this->notes = (string) ($purchaseOrder->notes ?? '');
        $this->items = $purchaseOrder->items->map(fn ($item): array => [
            'product_id' => (string) $item->product_id,
            'ordered_quantity' => (float) $item->ordered_quantity,
            'unit_cost' => (float) $item->unit_cost,
            'discount_amount' => (float) $item->discount_amount,
            'notes' => $item->notes,
        ])->all();

        if ($this->items === []) {
            $this->items = [$this->blankItem()];
        }
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

    public function saveDraft(PurchaseOrderWorkflow $workflow)
    {
        return $this->persist('draft', $workflow);
    }

    public function submitForApproval(PurchaseOrderWorkflow $workflow)
    {
        return $this->persist('submit', $workflow);
    }

    protected function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.ordered_quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function render(): View
    {
        return view('livewire.operations.purchase-order-form-panel', [
            'title' => $this->purchaseOrderId ? 'Edit Purchase Order' : 'Buat Purchase Order',
            'pageTitle' => $this->purchaseOrderId ? 'Edit Purchase Order' : 'Buat Purchase Order',
            'pageEyebrow' => 'Procurement',
            'pageDescription' => 'Kelola dokumen PO dengan workflow draft/submit tanpa logika di controller.',
            'generatedAt' => now('Asia/Jakarta')->translatedFormat('d F Y, H:i'),
            'backUrl' => route('purchase-orders'),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(['id', 'sku', 'name', 'cost_price']),
            'canEdit' => $this->canEdit,
        ]);
    }

    private function persist(string $intent, PurchaseOrderWorkflow $workflow)
    {
        $this->items = collect($this->items)
            ->map(fn (array $item): array => [
                'product_id' => $item['product_id'] ?? null,
                'ordered_quantity' => $item['ordered_quantity'] ?? null,
                'unit_cost' => $item['unit_cost'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'notes' => $item['notes'] ?? null,
            ])
            ->filter(fn (array $item): bool => filled($item['product_id']) || filled($item['ordered_quantity']) || filled($item['unit_cost']) || filled($item['notes']))
            ->values()
            ->all();

        if ($this->items === []) {
            $this->items = [$this->blankItem()];
        }

        $validated = $this->validate();

        $header = [
            'supplier_id' => (int) $validated['supplier_id'],
            'warehouse_id' => (int) $validated['warehouse_id'],
            'order_date' => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?: null,
            'terms' => filled($validated['terms']) ? trim((string) $validated['terms']) : null,
            'notes' => filled($validated['notes']) ? trim((string) $validated['notes']) : null,
        ];
        $lineItems = collect($validated['items'])->map(fn (array $item): array => [
            'product_id' => (int) $item['product_id'],
            'ordered_quantity' => (float) $item['ordered_quantity'],
            'unit_cost' => (float) $item['unit_cost'],
            'discount_amount' => (float) ($item['discount_amount'] ?? 0),
            'notes' => filled($item['notes'] ?? null) ? trim((string) $item['notes']) : null,
        ])->all();

        try {
            if ($this->purchaseOrderId !== null) {
                $purchaseOrder = PurchaseOrder::query()->findOrFail($this->purchaseOrderId);
                if (! $purchaseOrder->canBeEdited()) {
                    throw new DomainException('Purchase order pada status ini tidak bisa diedit lagi.');
                }

                $savedOrder = $workflow->update($purchaseOrder, $header, $lineItems, $intent);
                $message = 'Purchase order ' . $savedOrder->po_number . ' berhasil diperbarui.';
            } else {
                $savedOrder = $workflow->store($header, $lineItems, $intent);
                $this->purchaseOrderId = (int) $savedOrder->id;
                $message = 'Purchase order ' . $savedOrder->po_number . ' berhasil dibuat.';
            }
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());

            return null;
        }

        session()->flash('success', $message);

        return redirect()->route('purchase-orders');
    }

    /**
     * @return array{product_id: string, ordered_quantity: int, unit_cost: int, discount_amount: int, notes: string}
     */
    private function blankItem(): array
    {
        return [
            'product_id' => '',
            'ordered_quantity' => 1,
            'unit_cost' => 0,
            'discount_amount' => 0,
            'notes' => '',
        ];
    }
}
