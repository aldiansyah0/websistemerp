@extends('layouts.app')

@php
    $initialItems = old('items');
    if ($initialItems === null) {
        $initialItems = $items->map(fn ($item) => [
            'sales_transaction_item_id' => (string) $item->id,
            'product_id' => (string) $item->product_id,
            'quantity' => 0,
            'unit_price' => (float) $item->unit_price,
            'reason' => '',
            'notes' => '',
        ])->values()->all();
    }

    if (empty($initialItems)) {
        $initialItems = [[
            'sales_transaction_item_id' => '',
            'product_id' => '',
            'quantity' => 0,
            'unit_price' => 0,
            'reason' => '',
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
            <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $salesTransaction->invoice_number ?? $salesTransaction->transaction_number }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $salesTransaction->outlet?->name ?? '-' }} • {{ $salesTransaction->sold_at?->format('d M Y H:i') }}</p>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6" x-data="salesReturnForm(@js($items), @js($initialItems))">
            @csrf
            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)]">
                <div class="space-y-6">
                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Retur</span>
                                <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                                <textarea name="notes" rows="3" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Line Item Retur</h2>
                        <div class="mt-6 space-y-4">
                            <template x-for="(item, index) in rows" :key="index">
                                <div class="rounded-[22px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_repeat(2,minmax(0,0.7fr))_minmax(0,1fr)_auto]">
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Produk</span>
                                            <select :name="`items[${index}][sales_transaction_item_id]`" x-model="item.sales_transaction_item_id" @change="syncItem(index)" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Pilih line transaksi</option>
                                                <template x-for="line in sourceLines" :key="line.id">
                                                    <option :value="String(line.id)" x-text="`${line.product_name} (Sold: ${line.quantity})`"></option>
                                                </template>
                                            </select>
                                            <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Qty Retur</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Unit Price</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][unit_price]`" x-model="item.unit_price" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Alasan</span>
                                            <input type="text" :name="`items[${index}][reason]`" x-model="item.reason" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <div class="flex items-end">
                                            <button type="button" @click="remove(index)" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300">Hapus</button>
                                        </div>
                                    </div>
                                    <label class="mt-3 block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Catatan Item</span>
                                        <textarea :name="`items[${index}][notes]`" x-model="item.notes" rows="2" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                                    </label>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="add()" class="mt-4 inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">
                            Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Estimasi Refund</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white" x-text="money(totalRefund())"></p>
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
        function salesReturnForm(lines, initialRows) {
            return {
                sourceLines: lines.map((line) => ({
                    id: String(line.id),
                    product_id: String(line.product_id),
                    product_name: line.product?.name ?? '-',
                    quantity: Number(line.quantity || 0),
                    unit_price: Number(line.unit_price || 0),
                })),
                rows: initialRows.map((row) => ({
                    sales_transaction_item_id: row.sales_transaction_item_id ? String(row.sales_transaction_item_id) : '',
                    product_id: row.product_id ? String(row.product_id) : '',
                    quantity: Number(row.quantity || 0),
                    unit_price: Number(row.unit_price || 0),
                    reason: row.reason ?? '',
                    notes: row.notes ?? '',
                })),
                add() {
                    this.rows.push({
                        sales_transaction_item_id: '',
                        product_id: '',
                        quantity: 0,
                        unit_price: 0,
                        reason: '',
                        notes: '',
                    });
                },
                remove(index) {
                    if (this.rows.length === 1) {
                        this.rows[0] = { sales_transaction_item_id: '', product_id: '', quantity: 0, unit_price: 0, reason: '', notes: '' };
                        return;
                    }
                    this.rows.splice(index, 1);
                },
                syncItem(index) {
                    const source = this.sourceLines.find((line) => line.id === String(this.rows[index].sales_transaction_item_id));
                    if (!source) {
                        return;
                    }
                    this.rows[index].product_id = source.product_id;
                    if (!this.rows[index].unit_price || Number(this.rows[index].unit_price) === 0) {
                        this.rows[index].unit_price = source.unit_price;
                    }
                },
                totalRefund() {
                    return this.rows.reduce((sum, row) => sum + (Number(row.quantity || 0) * Number(row.unit_price || 0)), 0);
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

