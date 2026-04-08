@extends('layouts.app')

@php
    $initialItems = old('items');
    $initialPayments = old('payments');

    if ($initialItems === null || $initialItems === []) {
        $initialItems = [[
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_amount' => 0,
            'notes' => '',
        ]];
    }

    if ($initialPayments === null) {
        $initialPayments = [[
            'payment_method_id' => '',
            'amount' => '',
            'reference_number' => '',
            'approval_code' => '',
        ]];
    }
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.18),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(14,165,233,0.14),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.14),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(14,165,233,0.1),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                        Kembali ke POS
                    </a>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6" x-data="posForm(@js($productsForPicker), @js($initialItems), @js($initialPayments))" x-init="init()">
            @csrf

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)]">
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Outlet</span>
                                <select name="outlet_id" x-model="selectedOutlet" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih outlet</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" @selected((string) old('outlet_id') === (string) $outlet->id)>{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kasir</span>
                                <select name="cashier_employee_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih kasir</option>
                                    @foreach ($cashiers as $cashier)
                                        <option value="{{ $cashier->id }}" @selected((string) old('cashier_employee_id') === (string) $cashier->id)>{{ $cashier->full_name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Customer</span>
                                <select name="customer_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Walk-in / tanpa akun</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->code }} - {{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Customer Manual</span>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Transaksi</span>
                                <input type="datetime-local" name="sold_at" value="{{ old('sold_at', now()->format('Y-m-d\\TH:i')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Jatuh Tempo</span>
                                <input type="date" name="due_date" value="{{ old('due_date', now()->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                                <textarea name="notes" rows="3" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                                    Sales Line Items
                                </span>
                                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Barang yang dijual ke customer</h2>
                            </div>
                            <button type="button" @click="addItem()" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                Tambah Item
                            </button>
                        </div>

                        <div class="mt-5 rounded-[24px] border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-700 dark:text-blue-300">Barcode Centric Checkout</p>
                            <div class="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto]">
                                <label class="block">
                                    <span class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-700/70 dark:text-blue-300/70">Scan Barcode / SKU</span>
                                    <input
                                        type="text"
                                        x-ref="barcodeInput"
                                        x-model="barcodeInput"
                                        @keydown.enter.prevent="scanBarcode()"
                                        placeholder="Scan barcode lalu Enter (contoh: 8991002201001)"
                                        class="mt-2 w-full rounded-2xl border border-blue-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-blue-400 dark:border-blue-500/30 dark:bg-gray-950 dark:text-white"
                                    >
                                </label>
                                <button type="button" @click="scanBarcode()" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">
                                    Tambah ke Keranjang
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-blue-700/80 dark:text-blue-300/80">Scan diproses di sisi klien (Alpine.js), jadi kasir bisa checkout cepat tanpa roundtrip server.</p>
                            <template x-if="scanError">
                                <p class="mt-2 text-sm font-medium text-error-700 dark:text-error-300" x-text="scanError"></p>
                            </template>
                        </div>

                        <div class="mt-6 space-y-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-[28px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_repeat(4,minmax(0,0.7fr))_auto]">
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Produk</span>
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="syncProduct(index)" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Pilih produk</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="String(product.id)" x-text="`${product.sku} - ${product.name}${product.barcode ? ' / ' + product.barcode : ''}`"></option>
                                                </template>
                                            </select>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="stockCaption(item)"></p>
                                        </label>

                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Qty</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>

                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Harga</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][unit_price]`" x-model="item.unit_price" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>

                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Diskon</span>
                                            <input type="number" min="0" step="0.01" :name="`items[${index}][discount_amount]`" x-model="item.discount_amount" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>

                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Total</span>
                                            <div class="mt-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white" x-text="money(lineTotal(item))"></div>
                                        </label>

                                        <div class="flex items-end">
                                            <button type="button" @click="removeItem(index)" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-300 dark:hover:bg-error-500/10">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>

                                    <label class="mt-4 block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Catatan Item</span>
                                        <textarea :name="`items[${index}][notes]`" x-model="item.notes" rows="2" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                                    Payment Split
                                </span>
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Total seluruh metode pembayaran wajib sama dengan total belanja.</p>
                            </div>
                            <button type="button" @click="addPayment()" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                Tambah Payment
                            </button>
                        </div>

                        <div class="mt-6 space-y-4">
                            <template x-for="(payment, index) in payments" :key="index">
                                <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Metode</span>
                                            <select :name="`payments[${index}][payment_method_id]`" x-model="payment.payment_method_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                                <option value="">Pilih metode</option>
                                                @foreach ($paymentMethods as $method)
                                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Nominal</span>
                                            <input type="number" min="0" step="0.01" :name="`payments[${index}][amount]`" x-model="payment.amount" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Reference</span>
                                            <input type="text" :name="`payments[${index}][reference_number]`" x-model="payment.reference_number" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <label class="block">
                                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Approval</span>
                                            <input type="text" :name="`payments[${index}][approval_code]`" x-model="payment.approval_code" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                        </label>
                                        <div class="flex items-end">
                                            <button type="button" @click="removePayment(index)" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-300 dark:hover:bg-error-500/10">
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="grid gap-3">
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Gross</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="money(grossTotal())"></p>
                            </div>
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Discount</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="money(discountTotal())"></p>
                            </div>
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Net</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="money(netTotal())"></p>
                            </div>
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Paid Now</p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="money(paymentTotal())"></p>
                                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Balance <span x-text="money(balanceDue())"></span></p>
                                <template x-if="Math.abs(netTotal() - paymentTotal()) > 0.01">
                                    <p class="mt-2 text-sm font-medium text-error-700 dark:text-error-300">Total bayar harus sama dengan total belanja sebelum transaksi diposting.</p>
                                </template>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" :disabled="Math.abs(netTotal() - paymentTotal()) > 0.01 || netTotal() <= 0" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Post Transaksi
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
        function posForm(products, initialItems, initialPayments) {
            return {
                products,
                barcodeInput: '',
                scanError: '',
                barcodeMap: {},
                skuMap: {},
                selectedOutlet: '{{ old('outlet_id') }}',
                items: initialItems.map((item) => ({
                    product_id: item.product_id ? String(item.product_id) : '',
                    quantity: item.quantity ?? 1,
                    unit_price: item.unit_price ?? 0,
                    discount_amount: item.discount_amount ?? 0,
                    notes: item.notes ?? '',
                })),
                payments: initialPayments.map((payment) => ({
                    payment_method_id: payment.payment_method_id ? String(payment.payment_method_id) : '',
                    amount: payment.amount ?? '',
                    reference_number: payment.reference_number ?? '',
                    approval_code: payment.approval_code ?? '',
                })),
                init() {
                    this.products.forEach((product) => {
                        if (product.barcode) {
                            this.barcodeMap[String(product.barcode).trim().toUpperCase()] = product;
                        }
                        if (product.sku) {
                            this.skuMap[String(product.sku).trim().toUpperCase()] = product;
                        }
                    });
                    this.$nextTick(() => this.$refs.barcodeInput?.focus());
                },
                scanBarcode() {
                    this.scanError = '';
                    const token = String(this.barcodeInput || '').trim().toUpperCase();
                    if (!token) {
                        this.scanError = 'Barcode tidak boleh kosong.';
                        return;
                    }

                    const product = this.barcodeMap[token] || this.skuMap[token];
                    if (!product) {
                        this.scanError = `Barcode/SKU "${token}" tidak ditemukan.`;
                        this.barcodeInput = '';
                        this.$nextTick(() => this.$refs.barcodeInput?.focus());
                        return;
                    }

                    const lineIndex = this.items.findIndex((item) => String(item.product_id) === String(product.id));
                    if (lineIndex >= 0) {
                        this.items[lineIndex].quantity = Number(this.items[lineIndex].quantity || 0) + 1;
                    } else if (this.items.length === 1 && !this.items[0].product_id) {
                        this.items[0].product_id = String(product.id);
                        this.items[0].quantity = 1;
                        this.items[0].unit_price = Number(product.selling_price || 0);
                        this.items[0].discount_amount = 0;
                        this.items[0].notes = '';
                    } else {
                        this.items.push({
                            product_id: String(product.id),
                            quantity: 1,
                            unit_price: Number(product.selling_price || 0),
                            discount_amount: 0,
                            notes: '',
                        });
                    }

                    this.barcodeInput = '';
                    this.$nextTick(() => this.$refs.barcodeInput?.focus());
                },
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount_amount: 0, notes: '' });
                },
                removeItem(index) {
                    if (this.items.length === 1) {
                        this.items[0] = { product_id: '', quantity: 1, unit_price: 0, discount_amount: 0, notes: '' };
                        return;
                    }
                    this.items.splice(index, 1);
                },
                addPayment() {
                    this.payments.push({ payment_method_id: '', amount: '', reference_number: '', approval_code: '' });
                },
                removePayment(index) {
                    if (this.payments.length === 1) {
                        this.payments[0] = { payment_method_id: '', amount: '', reference_number: '', approval_code: '' };
                        return;
                    }
                    this.payments.splice(index, 1);
                },
                syncProduct(index) {
                    const product = this.products.find((candidate) => String(candidate.id) === String(this.items[index].product_id));
                    if (!product) {
                        return;
                    }
                    if (!this.items[index].unit_price || Number(this.items[index].unit_price) === 0) {
                        this.items[index].unit_price = product.selling_price;
                    }
                },
                stockCaption(item) {
                    const product = this.products.find((candidate) => String(candidate.id) === String(item.product_id));
                    if (!product || !this.selectedOutlet) {
                        return 'Pilih outlet untuk membaca stok.';
                    }
                    const stock = Number(product.stock_by_outlet?.[this.selectedOutlet] || 0);
                    return `Stok outlet: ${stock.toLocaleString('id-ID')} ${product.unit_of_measure}`;
                },
                lineTotal(item) {
                    return Math.max((Number(item.quantity || 0) * Number(item.unit_price || 0)) - Number(item.discount_amount || 0), 0);
                },
                grossTotal() {
                    return this.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
                },
                discountTotal() {
                    return this.items.reduce((sum, item) => sum + Number(item.discount_amount || 0), 0);
                },
                netTotal() {
                    return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
                },
                paymentTotal() {
                    return this.payments.reduce((sum, payment) => sum + Number(payment.amount || 0), 0);
                },
                balanceDue() {
                    return Math.max(this.netTotal() - this.paymentTotal(), 0);
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
