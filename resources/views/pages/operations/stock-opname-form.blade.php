@extends('layouts.app')

@php
    $opnameModel = $stockOpname ?? null;
    $initialItems = old('items');

    if ($initialItems === null) {
        $initialItems = $opnameModel?->items
            ?->map(fn ($item) => [
                'product_id' => (string) $item->product_id,
                'system_quantity' => (float) $item->system_quantity,
                'physical_quantity' => (float) $item->physical_quantity,
                'unit_cost' => (float) $item->unit_cost,
                'notes' => $item->notes,
            ])
            ->values()
            ->all() ?? [];
    }

    if (empty($initialItems)) {
        $initialItems = [[
            'product_id' => '',
            'system_quantity' => 0,
            'physical_quantity' => 0,
            'unit_cost' => 0,
            'notes' => '',
        ]];
    }
@endphp

@section('content')
    <div class="space-y-6">
        <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} • Snapshot {{ $generatedAt }}</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6" x-data="stockOpnameForm(@js($products), @js($initialItems))">
            @csrf
            @if ($submitMethod !== 'POST')
                @method($submitMethod)
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
                <div class="space-y-6">
                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Gudang</span>
                                <select name="warehouse_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih gudang</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $opnameModel?->warehouse_id) === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Opname</span>
                                <input type="date" name="opname_date" value="{{ old('opname_date', $opnameModel?->opname_date?->format('Y-m-d') ?? now()->toDateString()) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                                <textarea name="notes" rows="3" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes', $opnameModel?->notes) }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Item Opname</h2>
                            <button type="button" @click="addItem()" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                Tambah Item
                            </button>
                        </div>
                        <div class="mt-6 space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-[22px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_repeat(3,minmax(0,0.7fr))_auto]">
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Produk</span>
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="syncCost(index)" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Pilih produk</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="String(product.id)" x-text="`${product.sku} - ${product.name}`"></option>
                                                </template>
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Qty Sistem</span>
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][system_quantity]`" x-model="item.system_quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Qty Fisik</span>
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][physical_quantity]`" x-model="item.physical_quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Unit Cost</span>
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model="item.unit_cost" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <div class="flex items-end">
                                            <button type="button" @click="removeItem(index)" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <label class="mt-3 block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Catatan Item</span>
                                        <textarea :name="`items[${index}][notes]`" x-model="item.notes" rows="2" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Cepat</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Variance qty: <span class="font-semibold text-gray-900 dark:text-white" x-text="varianceQty()"></span></p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Variance value: <span class="font-semibold text-gray-900 dark:text-white" x-text="money(varianceValue())"></span></p>
                    </div>

                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" name="intent" value="draft" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                Simpan Draft
                            </button>
                            <button type="submit" name="intent" value="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">
                                Submit Approval
                            </button>
                            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function stockOpnameForm(products, initialItems) {
            return {
                products,
                items: initialItems.map((item) => ({
                    product_id: item.product_id ? String(item.product_id) : '',
                    system_quantity: item.system_quantity ?? 0,
                    physical_quantity: item.physical_quantity ?? 0,
                    unit_cost: item.unit_cost ?? 0,
                    notes: item.notes ?? '',
                })),
                addItem() {
                    this.items.push({ product_id: '', system_quantity: 0, physical_quantity: 0, unit_cost: 0, notes: '' });
                },
                removeItem(index) {
                    if (this.items.length === 1) {
                        this.items[0] = { product_id: '', system_quantity: 0, physical_quantity: 0, unit_cost: 0, notes: '' };
                        return;
                    }
                    this.items.splice(index, 1);
                },
                syncCost(index) {
                    const product = this.products.find((candidate) => String(candidate.id) === String(this.items[index].product_id));
                    if (!product) {
                        return;
                    }
                    if (!this.items[index].unit_cost || Number(this.items[index].unit_cost) === 0) {
                        this.items[index].unit_cost = Number(product.cost_price || 0);
                    }
                },
                varianceQty() {
                    const value = this.items.reduce((sum, item) => sum + (Number(item.physical_quantity || 0) - Number(item.system_quantity || 0)), 0);
                    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
                },
                varianceValue() {
                    return this.items.reduce((sum, item) => sum + ((Number(item.physical_quantity || 0) - Number(item.system_quantity || 0)) * Number(item.unit_cost || 0)), 0);
                },
                money(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(value || 0);
                },
            };
        }
    </script>
@endpush

