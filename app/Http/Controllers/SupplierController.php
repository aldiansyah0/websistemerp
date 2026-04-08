<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\AnalyticsCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function create(): View
    {
        return view('pages.operations.livewire.supplier-form', [
            'title' => 'Tambah Supplier',
            'supplierId' => null,
        ]);
    }

    public function store(SupplierRequest $request, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $supplier = Supplier::query()->create($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('supplier')->with('success', 'Supplier ' . $supplier->name . ' berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('pages.operations.livewire.supplier-form', [
            'title' => 'Edit Supplier',
            'supplierId' => (int) $supplier->id,
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $supplier->update($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('supplier')->with('success', 'Supplier ' . $supplier->name . ' berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $supplier->loadCount(['products', 'purchaseOrders']);

        if ($supplier->products_count > 0 || $supplier->purchase_orders_count > 0) {
            return redirect()->route('supplier')->with('error', 'Supplier tidak bisa dihapus karena sudah dipakai di produk atau purchase order.');
        }

        $supplier->delete();
        $analyticsCacheService->invalidate();

        return redirect()->route('supplier')->with('success', 'Supplier berhasil dihapus dari master data.');
    }
}
