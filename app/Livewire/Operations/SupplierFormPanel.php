<?php

namespace App\Livewire\Operations;

use App\Models\Supplier;
use App\Models\Tenant;
use App\Services\AnalyticsCacheService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SupplierFormPanel extends Component
{
    public ?int $supplierId = null;

    public string $code = '';
    public string $name = '';
    public string $contact_person = '';
    public string $email = '';
    public string $phone = '';
    public string $city = '';
    public string $address = '';
    public int $lead_time_days = 0;
    public int $payment_term_days = 0;
    public float $fill_rate = 0.0;
    public float $reject_rate = 0.0;
    public float $rating = 0.0;
    public string $notes = '';
    public bool $is_active = true;

    public function mount(?int $supplierId = null): void
    {
        $this->supplierId = $supplierId;

        if ($supplierId === null) {
            return;
        }

        $supplier = Supplier::query()->findOrFail($supplierId);

        $this->code = (string) $supplier->code;
        $this->name = (string) $supplier->name;
        $this->contact_person = (string) ($supplier->contact_person ?? '');
        $this->email = (string) ($supplier->email ?? '');
        $this->phone = (string) ($supplier->phone ?? '');
        $this->city = (string) ($supplier->city ?? '');
        $this->address = (string) ($supplier->address ?? '');
        $this->lead_time_days = (int) $supplier->lead_time_days;
        $this->payment_term_days = (int) $supplier->payment_term_days;
        $this->fill_rate = (float) $supplier->fill_rate;
        $this->reject_rate = (float) $supplier->reject_rate;
        $this->rating = (float) $supplier->rating;
        $this->notes = (string) ($supplier->notes ?? '');
        $this->is_active = (bool) $supplier->is_active;
    }

    public function save(AnalyticsCacheService $analyticsCacheService)
    {
        try {
            // Validate data
            $validated = $this->validate();

            // Resolve tenant ID
            $tenantId = $this->resolveTenantId();

            if ($tenantId === null) {
                Log::warning('Unable to resolve tenant ID for supplier creation');
                $this->dispatch('notify', type: 'error', message: 'Error: Tidak bisa menentukan tenant. Hubungi administrator.');
                return;
            }

            // Prepare payload
            $payload = [
                'tenant_id' => $tenantId,
                'code' => trim((string) $validated['code']),
                'name' => trim((string) $validated['name']),
                'contact_person' => filled($validated['contact_person']) ? trim((string) $validated['contact_person']) : null,
                'email' => filled($validated['email']) ? trim((string) $validated['email']) : null,
                'phone' => filled($validated['phone']) ? trim((string) $validated['phone']) : null,
                'city' => filled($validated['city']) ? trim((string) $validated['city']) : null,
                'address' => filled($validated['address']) ? trim((string) $validated['address']) : null,
                'lead_time_days' => (int) $validated['lead_time_days'],
                'payment_term_days' => (int) $validated['payment_term_days'],
                'fill_rate' => (float) $validated['fill_rate'],
                'reject_rate' => (float) $validated['reject_rate'],
                'rating' => (float) $validated['rating'],
                'notes' => filled($validated['notes']) ? trim((string) $validated['notes']) : null,
                'is_active' => (bool) $validated['is_active'],
            ];

            Log::info('Creating/updating supplier', ['payload' => $payload]);

            // Create or update
            if ($this->supplierId !== null) {
                $supplier = Supplier::query()->findOrFail($this->supplierId);
                $supplier->update($payload);
                $message = 'Supplier ' . $supplier->name . ' berhasil diperbarui.';
                Log::info('Supplier updated successfully', ['supplier_id' => $supplier->id]);
            } else {
                $supplier = Supplier::query()->create($payload);
                $this->supplierId = (int) $supplier->id;
                $message = 'Supplier ' . $supplier->name . ' berhasil ditambahkan.';
                Log::info('Supplier created successfully', ['supplier_id' => $supplier->id]);
            }

            // Invalidate cache
            $analyticsCacheService->invalidate();

            // Send notification & redirect
            session()->flash('success', $message);
            return redirect()->route('supplier');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed - errors are auto-displayed by Livewire
            Log::warning('Validation failed for supplier', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            // Log the error
            Log::error('Supplier save error: ' . $e->getMessage(), [
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
            'code' => ['required', 'string', 'max:40', Rule::unique('suppliers', 'code')->ignore($this->supplierId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($this->supplierId)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($this->supplierId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'fill_rate' => ['nullable', 'numeric', 'between:0,100'],
            'reject_rate' => ['nullable', 'numeric', 'between:0,100'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function render(): View
    {
        return view('livewire.operations.supplier-form-panel', [
            'title' => $this->supplierId ? 'Edit Supplier' : 'Tambah Supplier',
            'pageTitle' => $this->supplierId ? 'Edit Supplier' : 'Tambah Supplier',
            'pageEyebrow' => 'Master Supplier',
            'pageDescription' => $this->supplierId
                ? 'Perbarui profil supplier agar SLA procurement tetap terkendali.'
                : 'Tambahkan supplier baru untuk mendukung kapasitas procurement retail.',
            'backUrl' => route('supplier'),
            'generatedAt' => now('Asia/Jakarta')->translatedFormat('d F Y, H:i'),
        ]);
    }
}
