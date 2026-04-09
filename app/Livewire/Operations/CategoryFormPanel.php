<?php

namespace App\Livewire\Operations;

use App\Models\Category;
use App\Models\Tenant;
use App\Services\AnalyticsCacheService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        try {
            // Validate data
            $validated = $this->validate();

            // Resolve tenant ID
            $tenantId = $this->resolveTenantId();

            if ($tenantId === null) {
                Log::warning('Unable to resolve tenant ID for category creation');
                $this->addError('general', 'Error: Tidak bisa menentukan tenant. Hubungi administrator.');
                return;
            }

            // Prepare payload
            $payload = [
                'tenant_id' => $tenantId,
                'code' => trim((string) $validated['code']),
                'name' => trim((string) $validated['name']),
                'slug' => Str::slug((string) $validated['name']),
                'description' => filled($validated['description']) ? trim((string) $validated['description']) : null,
                'sort_order' => (int) $validated['sort_order'],
                'is_active' => (bool) $validated['is_active'],
            ];

            Log::info('Creating/updating category', ['payload' => $payload]);

            // Create or update
            if ($this->categoryId !== null) {
                $category = Category::query()->findOrFail($this->categoryId);
                $category->update($payload);
                $message = 'Kategori ' . $category->name . ' berhasil diperbarui.';
                Log::info('Category updated successfully', ['category_id' => $category->id]);
            } else {
                $category = Category::query()->create($payload);
                $this->categoryId = (int) $category->id;
                $message = 'Kategori ' . $category->name . ' berhasil ditambahkan.';
                Log::info('Category created successfully', ['category_id' => $category->id]);
            }

            // Invalidate cache
            $analyticsCacheService->invalidate();

            // Send notification & redirect
            session()->flash('success', $message);
            return redirect()->route('kategori');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed - errors are auto-displayed by Livewire
            Log::warning('Validation failed for category', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            // Log the error
            Log::error('Category save error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('general', 'Terjadi error saat menyimpan: ' . $e->getMessage());
        }
    }

    private function resolveTenantId(): ?int
    {
        $user = Auth::user();
        if ($user !== null && $user->tenant_id !== null) {
            return (int) $user->tenant_id;
        }

        $defaultTenant = Tenant::query()->where('code', 'default')->value('id');
        return $defaultTenant !== null ? (int) $defaultTenant : null;
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
