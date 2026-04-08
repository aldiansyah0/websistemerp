<?php

namespace App\Livewire\Operations;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\AnalyticsCacheService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductFormPanel extends Component
{
    public ?int $productId = null;

    public ?int $category_id = null;
    public ?int $primary_supplier_id = null;
    public string $sku = '';
    public string $barcode = '';
    public string $name = '';
    public string $description = '';
    public string $unit_of_measure = 'pcs';
    public float $cost_price = 0.0;
    public float $selling_price = 0.0;
    public float $daily_run_rate = 0.0;
    public float $reorder_level = 0.0;
    public float $reorder_quantity = 0.0;
    public ?int $shelf_life_days = null;
    public string $status = Product::STATUS_ACTIVE;
    public bool $is_featured = false;

    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;

        if ($productId === null) {
            return;
        }

        $product = Product::query()->findOrFail($productId);

        $this->category_id = $product->category_id;
        $this->primary_supplier_id = $product->primary_supplier_id;
        $this->sku = (string) $product->sku;
        $this->barcode = (string) ($product->barcode ?? '');
        $this->name = (string) $product->name;
        $this->description = (string) ($product->description ?? '');
        $this->unit_of_measure = (string) $product->unit_of_measure;
        $this->cost_price = (float) $product->cost_price;
        $this->selling_price = (float) $product->selling_price;
        $this->daily_run_rate = (float) $product->daily_run_rate;
        $this->reorder_level = (float) $product->reorder_level;
        $this->reorder_quantity = (float) $product->reorder_quantity;
        $this->shelf_life_days = $product->shelf_life_days !== null ? (int) $product->shelf_life_days : null;
        $this->status = (string) $product->status;
        $this->is_featured = (bool) $product->is_featured;
    }

    public function save(AnalyticsCacheService $analyticsCacheService)
    {
        $validated = $this->validate();

        $payload = [
            'category_id' => (int) $validated['category_id'],
            'primary_supplier_id' => $validated['primary_supplier_id'] ? (int) $validated['primary_supplier_id'] : null,
            'sku' => trim((string) $validated['sku']),
            'barcode' => filled($validated['barcode']) ? trim((string) $validated['barcode']) : null,
            'name' => trim((string) $validated['name']),
            'slug' => Str::slug(trim((string) $validated['name']) . '-' . trim((string) $validated['sku'])),
            'description' => filled($validated['description']) ? trim((string) $validated['description']) : null,
            'unit_of_measure' => (string) $validated['unit_of_measure'],
            'cost_price' => (float) $validated['cost_price'],
            'selling_price' => (float) $validated['selling_price'],
            'daily_run_rate' => (float) $validated['daily_run_rate'],
            'reorder_level' => (float) $validated['reorder_level'],
            'reorder_quantity' => (float) $validated['reorder_quantity'],
            'shelf_life_days' => $validated['shelf_life_days'] !== null ? (int) $validated['shelf_life_days'] : null,
            'status' => (string) $validated['status'],
            'is_featured' => (bool) $validated['is_featured'],
        ];

        if ($this->productId !== null) {
            $product = Product::query()->findOrFail($this->productId);
            $product->update($payload);
            $message = 'Master produk berhasil diperbarui.';
        } else {
            $product = Product::query()->create($payload);
            $this->productId = (int) $product->id;
            $message = 'Produk baru berhasil ditambahkan ke master data.';
        }

        $analyticsCacheService->invalidate();
        session()->flash('success', $message);

        return redirect()->route('produk');
    }

    protected function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'primary_supplier_id' => ['nullable', 'exists:suppliers,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->productId)],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')->ignore($this->productId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_of_measure' => ['required', Rule::in(['pcs', 'box', 'btl', 'cup', 'pack'])],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'gte:cost_price'],
            'daily_run_rate' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'shelf_life_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys(Product::statusOptions()))],
            'is_featured' => ['required', 'boolean'],
        ];
    }

    public function render(): View
    {
        return view('livewire.operations.product-form-panel', [
            'title' => $this->productId ? 'Edit Produk' : 'Tambah Produk',
            'pageTitle' => $this->productId ? 'Edit Produk' : 'Tambah Produk',
            'pageEyebrow' => 'Master Produk',
            'pageDescription' => $this->productId
                ? 'Perbarui master produk agar pricing dan stock policy tetap sinkron.'
                : 'Tambahkan SKU baru lengkap dengan pricing, kategori, dan supplier utama.',
            'backUrl' => route('produk'),
            'generatedAt' => now('Asia/Jakarta')->translatedFormat('d F Y, H:i'),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'statusOptions' => Product::statusOptions(),
            'unitOptions' => ['pcs', 'box', 'btl', 'cup', 'pack'],
            'createCategoryUrl' => route('categories.create'),
            'createSupplierUrl' => route('suppliers.create'),
        ]);
    }
}
