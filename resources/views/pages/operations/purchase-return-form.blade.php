@extends('layouts.app')

@php
    $initialItems = old('items');
    if ($initialItems === null || $initialItems === []) {
        $initialItems = [[
            'purchase_order_item_id' => '',
            'product_id' => '',
            'quantity' => 0,
            'unit_cost' => 0,
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
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6" x-data="purchaseReturnForm(@js($purchaseOrders), @js($products), @js($initialItems))">
            @csrf
            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)]">
                <div class="space-y-6">
                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Purchase Order (Opsional)</span>
                                <select name="purchase_order_id" x-model="selectedPurchaseOrder" @change="syncFromPurchaseOrder()" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih PO</option>
                                    @foreach ($purchaseOrders as $purchaseOrder)
                                        <option value="{{ $purchaseOrder->id }}">{{ $purchaseOrder->po_number }} • {{ $purchaseOrder->supplier?->name }} • {{ $purchaseOrder->warehouse?->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Supplier</span>
                                <select name="supplier_id" x-model="supplierId" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Warehouse</span>
                                <select name="warehouse_id" x-model="warehouseId" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih warehouse</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </label>
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
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Item Retur</h2>
                            <button type="button" @click="addItem()" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Tambah Baris</button>
                        </div>
                        <div class="mt-6 space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-[22px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_repeat(2,minmax(0,0.7fr))_minmax(0,1fr)_auto]">
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Produk/PO Item</span>
                                            <select :name="`items[${index}][purchase_order_item_id]`" x-model="item.purchase_order_item_id" @change="syncLine(index)" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Pilih PO line</option>
                                                <template x-for="line in poLines" :key="line.id">
                                                    <option :value="String(line.id)" x-text="`${line.product_name} (Received: ${line.received_quantity})`"></option>
                                                </template>
                                            </select>
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Atau pilih produk manual</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="String(product.id)" x-text="`${product.sku} - ${product.name}`"></option>
                                                </template>
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Qty</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Unit Cost</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][unit_cost]`" x-model="item.unit_cost" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Alasan</span>
                                            <input type="text" :name="`items[${index}][reason]`" x-model="item.reason" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <div class="flex items-end">
                                            <button type="button" @click="removeItem(index)" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300">Hapus</button>
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
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Estimasi Total Retur</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white" x-text="money(totalAmount())"></p>
                    </div>
                    <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" name="intent" value="draft" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Simpan Draft</button>
                            <button type="submit" name="intent" value="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Submit Approval</button>
                            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Batal</a>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function purchaseReturnForm(purchaseOrders, products, initialItems) {
            return {
                purchaseOrders,
                products: products.map((product) => ({
                    id: String(product.id),
                    sku: product.sku,
                    name: product.name,
                })),
                selectedPurchaseOrder: '{{ old('purchase_order_id') }}',
                supplierId: '{{ old('supplier_id') }}',
                warehouseId: '{{ old('warehouse_id') }}',
                poLines: [],
                items: initialItems.map((item) => ({
                    purchase_order_item_id: item.purchase_order_item_id ? String(item.purchase_order_item_id) : '',
                    product_id: item.product_id ? String(item.product_id) : '',
                    quantity: Number(item.quantity || 0),
                    unit_cost: Number(item.unit_cost || 0),
                    reason: item.reason ?? '',
                    notes: item.notes ?? '',
                })),
                syncFromPurchaseOrder() {
                    const po = this.purchaseOrders.find((candidate) => String(candidate.id) === String(this.selectedPurchaseOrder));
                    if (!po) {
                        this.poLines = [];
                        return;
                    }

                    this.supplierId = String(po.supplier_id || '');
                    this.warehouseId = String(po.warehouse_id || '');
                    this.poLines = (po.items || []).map((line) => ({
                        id: String(line.id),
                        product_id: String(line.product_id),
                        product_name: line.product?.name ?? '-',
                        received_quantity: Number(line.received_quantity || 0),
                        unit_cost: Number(line.unit_cost || 0),
                    }));
                },
                syncLine(index) {
                    const line = this.poLines.find((candidate) => candidate.id === String(this.items[index].purchase_order_item_id));
                    if (!line) {
                        return;
                    }
                    this.items[index].product_id = line.product_id;
                    if (!this.items[index].unit_cost || Number(this.items[index].unit_cost) === 0) {
                        this.items[index].unit_cost = line.unit_cost;
                    }
                },
                addItem() {
                    this.items.push({
                        purchase_order_item_id: '',
                        product_id: '',
                        quantity: 0,
                        unit_cost: 0,
                        reason: '',
                        notes: '',
                    });
                },
                removeItem(index) {
                    if (this.items.length === 1) {
                        this.items[0] = { purchase_order_item_id: '', product_id: '', quantity: 0, unit_cost: 0, reason: '', notes: '' };
                        return;
                    }
                    this.items.splice(index, 1);
                },
                totalAmount() {
                    return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_cost || 0)), 0);
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

