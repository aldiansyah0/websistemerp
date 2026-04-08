<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\AnalyticsCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function create(): View
    {
        return view('pages.operations.livewire.product-form', [
            'title' => 'Tambah Produk',
            'productId' => null,
        ]);
    }

    public function store(ProductRequest $request, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        Product::query()->create($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('produk')->with('success', 'Produk baru berhasil ditambahkan ke master data.');
    }

    public function edit(Product $product): View
    {
        return view('pages.operations.livewire.product-form', [
            'title' => 'Edit Produk',
            'productId' => (int) $product->id,
        ]);
    }

    public function update(ProductRequest $request, Product $product, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $product->update($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('produk')->with('success', 'Master produk berhasil diperbarui.');
    }

    public function destroy(Product $product, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $product->loadCount(['inventoryLedgers', 'purchaseOrderItems']);

        if ($product->inventory_ledgers_count > 0 || $product->purchase_order_items_count > 0) {
            return redirect()->route('produk')->with('error', 'Produk tidak bisa dihapus karena sudah dipakai di stok atau purchase order.');
        }

        $product->delete();
        $analyticsCacheService->invalidate();

        return redirect()->route('produk')->with('success', 'Produk berhasil dihapus dari master data.');
    }
}
