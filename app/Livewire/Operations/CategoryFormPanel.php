<?php

namespace App\Livewire\Operations;

use App\Models\Category;
use App\Services\AnalyticsCacheService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CategoryFormPanel extends Component
{
    public ?int $categoryId = null;

    public string $code = '';
    public string $name = '';
    public string $description = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public function mount(?int $categoryId = null): void
    {
        $this->categoryId = $categoryId;

        if ($categoryId === null) {
            return;
        }

        $category = Category::query()->findOrFail($categoryId);

        $this->code = (string) $category->code;
        $this->name = (string) $category->name;
        $this->description = (string) ($category->description ?? '');
        $this->sort_order = (int) $category->sort_order;
        $this->is_active = (bool) $category->is_active;
    }

    public function save(AnalyticsCacheService $analyticsCacheService)
    {
        $validated = $this->validate();

        $payload = [
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
            'slug' => Str::slug((string) $validated['name']),
            'description' => filled($validated['description']) ? trim((string) $validated['description']) : null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
        ];

        if ($this->categoryId !== null) {
            $category = Category::query()->findOrFail($this->categoryId);
            $category->update($payload);
            $message = 'Kategori ' . $category->name . ' berhasil diperbarui.';
        } else {
            $category = Category::query()->create($payload);
            $this->categoryId = (int) $category->id;
            $message = 'Kategori ' . $category->name . ' berhasil ditambahkan.';
        }

        $analyticsCacheService->invalidate();
        session()->flash('success', $message);

        return redirect()->route('kategori');
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('categories', 'code')->ignore($this->categoryId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->categoryId)],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function render(): View
    {
        return view('livewire.operations.category-form-panel', [
            'title' => $this->categoryId ? 'Edit Kategori' : 'Tambah Kategori',
            'pageTitle' => $this->categoryId ? 'Edit Kategori' : 'Tambah Kategori',
            'pageEyebrow' => 'Master Kategori',
            'pageDescription' => $this->categoryId
                ? 'Perbarui struktur kategori agar assortment retail tetap konsisten.'
                : 'Tambahkan kategori baru untuk memperkuat struktur master data produk.',
            'backUrl' => route('kategori'),
            'generatedAt' => now('Asia/Jakarta')->translatedFormat('d F Y, H:i'),
        ]);
    }
}
