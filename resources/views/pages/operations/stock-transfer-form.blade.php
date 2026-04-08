@extends('layouts.app')

@php
    $transferModel = $stockTransfer ?? null;
    $initialItems = old('items');

    if ($initialItems === null) {
        $initialItems = $transferModel?->items?->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'requested_quantity' => (float) $item->requested_quantity,
            'notes' => $item->notes,
        ])->values()->all() ?? [];
    }

    if (empty($initialItems)) {
        $initialItems = [[
            'product_id' => '',
            'requested_quantity' => 1,
            'notes' => '',
        ]];
    }
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(34,197,94,0.14),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(34,197,94,0.1),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                    <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Kembali ke Mutasi
                    </a>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6" x-data="stockTransferForm(@js($productsForPicker), @js($initialItems))">
            @csrf
            @if ($submitMethod !== 'POST')
                @method($submitMethod)
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)]">
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Gudang Asal</span>
                                <select name="source_warehouse_id" x-model="sourceWarehouseId" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih gudang asal</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected((string) old('source_warehouse_id', $transferModel?->source_warehouse_id) === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Gudang Tujuan</span>
                                <select name="destination_warehouse_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih gudang tujuan</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected((string) old('destination_warehouse_id', $transferModel?->destination_warehouse_id) === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Request Date</span>
                                <input type="date" name="request_date" value="{{ old('request_date', $transferModel?->request_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Expected Receipt</span>
                                <input type="date" name="expected_receipt_date" value="{{ old('expected_receipt_date', $transferModel?->expected_receipt_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                                <textarea name="notes" rows="3" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes', $transferModel?->notes) }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                                    Transfer Items
                                </span>
                                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">SKU yang dipindahkan</h2>
                            </div>
                            <button type="button" @click="addItem()" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                Tambah Item
                            </button>
                        </div>

                        <div class="mt-6 space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-[28px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,0.7fr)_minmax(0,1fr)_auto]">
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Produk</span>
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Pilih produk</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="String(product.id)" x-text="`${product.sku} - ${product.name}`"></option>
                                                </template>
                                            </select>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="stockCaption(item)"></p>
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Qty</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][requested_quantity]`" x-model="item.requested_quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Catatan</span>
                                            <input type="text" :name="`items[${index}][notes]`" x-model="item.notes" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <div class="flex items-end">
                                            <button type="button" @click="removeItem(index)" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-300 dark:hover:bg-error-500/10">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-3">
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Line Items</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="items.length"></p>
                            </div>
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Requested Qty</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="qtyTotal()"></p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" name="intent" value="draft" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                Simpan Draft
                            </button>
                            <button type="submit" name="intent" value="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Submit Approval
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function stockTransferForm(products, initialItems) {
            return {
                products,
                sourceWarehouseId: '{{ old('source_warehouse_id', $transferModel?->source_warehouse_id) }}',
                items: initialItems.map((item) => ({
                    product_id: item.product_id ? String(item.product_id) : '',
                    requested_quantity: item.requested_quantity ?? 1,
                    notes: item.notes ?? '',
                })),
                addItem() {
                    this.items.push({ product_id: '', requested_quantity: 1, notes: '' });
                },
                removeItem(index) {
                    if (this.items.length === 1) {
                        this.items[0] = { product_id: '', requested_quantity: 1, notes: '' };
                        return;
                    }
                    this.items.splice(index, 1);
                },
                qtyTotal() {
                    return this.items.reduce((sum, item) => sum + Number(item.requested_quantity || 0), 0).toLocaleString('id-ID');
                },
                stockCaption(item) {
                    const product = this.products.find((candidate) => String(candidate.id) === String(item.product_id));
                    if (!product || !this.sourceWarehouseId) {
                        return 'Pilih gudang asal untuk membaca stok.';
                    }
                    const stock = Number(product.stock_by_warehouse?.[this.sourceWarehouseId] || 0);
                    return `Stok asal: ${stock.toLocaleString('id-ID')} ${product.unit_of_measure}`;
                },
            };
        }
    </script>
@endpush
