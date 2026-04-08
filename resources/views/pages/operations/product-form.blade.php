@extends('layouts.app')

@php
    $productModel = $product ?? null;
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(34,197,94,0.14),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(34,197,94,0.1),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
            <div class="relative px-6 py-7 md:px-8 md:py-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                            <span>{{ $pageEyebrow }}</span>
                            <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Snapshot {{ $generatedAt }}</span>
                        </div>
                        <h1 class="mt-5 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">{{ $pageTitle }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">{{ $pageDescription }}</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            Kembali ke Master Produk
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6">
            @csrf
            @if ($submitMethod !== 'POST')
                @method($submitMethod)
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)]">
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                            Data Dasar
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">SKU</span>
                                <input type="text" name="sku" value="{{ old('sku', $productModel?->sku) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none ring-0 transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Barcode</span>
                                <input type="text" name="barcode" value="{{ old('barcode', $productModel?->barcode) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none ring-0 transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Produk</span>
                                <input type="text" name="name" value="{{ old('name', $productModel?->name) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none ring-0 transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kategori</span>
                                <select name="category_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('category_id', $productModel?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ $createCategoryUrl }}" class="mt-2 inline-flex text-xs font-semibold text-blue-light-700 transition hover:text-blue-light-600 dark:text-blue-light-300 dark:hover:text-blue-light-200">
                                    + Tambah kategori baru
                                </a>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Supplier Utama</span>
                                <select name="primary_supplier_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Belum dipilih</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected((string) old('primary_supplier_id', $productModel?->primary_supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ $createSupplierUrl }}" class="mt-2 inline-flex text-xs font-semibold text-blue-light-700 transition hover:text-blue-light-600 dark:text-blue-light-300 dark:hover:text-blue-light-200">
                                    + Tambah supplier baru
                                </a>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Satuan</span>
                                <select name="unit_of_measure" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    @foreach ($unitOptions as $unit)
                                        <option value="{{ $unit }}" @selected(old('unit_of_measure', $productModel?->unit_of_measure ?? 'pcs') === $unit)>{{ strtoupper($unit) }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status</span>
                                <select name="status" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $productModel?->status ?? 'active') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Deskripsi</span>
                                <textarea name="description" rows="5" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('description', $productModel?->description) }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                            Pricing dan Reorder
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Cost Price</span>
                                <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $productModel?->cost_price) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Selling Price</span>
                                <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', $productModel?->selling_price) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Daily Run Rate</span>
                                <input type="number" step="0.01" min="0" name="daily_run_rate" value="{{ old('daily_run_rate', $productModel?->daily_run_rate ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Shelf Life Days</span>
                                <input type="number" min="0" name="shelf_life_days" value="{{ old('shelf_life_days', $productModel?->shelf_life_days) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reorder Level</span>
                                <input type="number" step="0.01" min="0" name="reorder_level" value="{{ old('reorder_level', $productModel?->reorder_level ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reorder Quantity</span>
                                <input type="number" step="0.01" min="0" name="reorder_quantity" value="{{ old('reorder_quantity', $productModel?->reorder_quantity ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                            Governance
                        </span>

                        <div class="mt-6 space-y-4">
                            <label class="flex items-start gap-3 rounded-[24px] border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $productModel?->is_featured)) class="mt-1 h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">Featured SKU</span>
                                    <span class="mt-1 block text-sm leading-6 text-gray-500 dark:text-gray-400">Tandai produk ini sebagai SKU prioritas agar lebih mudah ditarik ke dashboard dan program promosi.</span>
                                </span>
                            </label>

                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Checklist master data</p>
                                <ul class="mt-3 space-y-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    <li>SKU harus unik dan stabil karena akan dipakai di POS, stok, dan pembelian.</li>
                                    <li>Cost dan selling price menjadi dasar margin serta valuasi inventory.</li>
                                    <li>Daily run rate dan reorder policy menentukan sinyal replenishment di dashboard.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Finalisasi</p>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Simpan perubahan untuk memperbarui kontrak data master produk yang dipakai modul stok dan procurement.</p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Simpan Produk
                            </button>
                            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection
