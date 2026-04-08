<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\AnalyticsCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function create(): View
    {
        return view('pages.operations.livewire.category-form', [
            'title' => 'Tambah Kategori',
            'categoryId' => null,
        ]);
    }

    public function store(CategoryRequest $request, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $category = Category::query()->create($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('kategori')->with('success', 'Kategori ' . $category->name . ' berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('pages.operations.livewire.category-form', [
            'title' => 'Edit Kategori',
            'categoryId' => (int) $category->id,
        ]);
    }

    public function update(CategoryRequest $request, Category $category, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $category->update($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('kategori')->with('success', 'Kategori ' . $category->name . ' berhasil diperbarui.');
    }

    public function destroy(Category $category, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $category->loadCount('products');

        if ($category->products_count > 0) {
            return redirect()->route('kategori')->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh master produk.');
        }

        $category->delete();
        $analyticsCacheService->invalidate();

        return redirect()->route('kategori')->with('success', 'Kategori berhasil dihapus dari master data.');
    }
}
