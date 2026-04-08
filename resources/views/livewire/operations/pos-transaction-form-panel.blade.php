<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Kembali ke POS</a>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.12fr)_minmax(320px,0.88fr)]">
            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Outlet</span>
                            <select wire:model.defer="outlet_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih outlet</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kasir</span>
                            <select wire:model.defer="cashier_employee_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih kasir</option>
                                @foreach ($cashiers as $cashier)
                                    <option value="{{ $cashier->id }}">{{ $cashier->full_name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Customer</span>
                            <select wire:model.defer="customer_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Walk-in / tanpa akun</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->code }} - {{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Customer Manual</span>
                            <input type="text" wire:model.defer="customer_name" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Transaksi</span>
                            <input type="datetime-local" wire:model.defer="sold_at" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Jatuh Tempo</span>
                            <input type="date" wire:model.defer="due_date" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                            <textarea rows="3" wire:model.defer="notes" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        </label>
                    </div>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-700 dark:text-blue-300">Barcode Centric Checkout</p>
                        <label class="mt-3 block">
                            <span class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-700/70 dark:text-blue-300/70">Scan Barcode / SKU</span>
                            <input
                                type="text"
                                wire:model.defer="barcode"
                                wire:keydown.enter.prevent="scanBarcode"
                                placeholder="Scan barcode lalu Enter (contoh: 8991002201001)"
                                class="mt-2 w-full rounded-2xl border border-blue-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-blue-500/30 dark:bg-gray-950 dark:text-white"
                            >
                        </label>
                        <button type="button" wire:click="scanBarcode" class="mt-3 inline-flex items-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Tambah ke Keranjang</button>
                        @if ($scanError)
                            <p class="mt-2 text-sm font-medium text-error-700 dark:text-error-300">{{ $scanError }}</p>
                        @endif
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Sales Line Items</h2>
                        <button type="button" wire:click="addItem" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Tambah Item</button>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($items as $index => $item)
                            @php
                                $quantity = (float) ($item['quantity'] ?? 0);
                                $price = (float) ($item['unit_price'] ?? 0);
                                $discount = (float) ($item['discount_amount'] ?? 0);
                                $lineTotal = max(($quantity * $price) - $discount, 0);
                            @endphp
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70" wire:key="pos-item-{{ $index }}">
                                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.6fr)_repeat(3,minmax(0,0.8fr))_minmax(0,1fr)_auto]">
                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Produk</span>
                                        <select wire:model.defer="items.{{ $index }}.product_id" wire:change="syncProduct({{ $index }})" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">Pilih produk</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}@if($product->barcode) / {{ $product->barcode }}@endif</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Qty</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="items.{{ $index }}.quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Harga</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="items.{{ $index }}.unit_price" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Diskon</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="items.{{ $index }}.discount_amount" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <div class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Line Total</span>
                                        <div class="mt-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="flex items-end">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300">Hapus</button>
                                    </div>
                                </div>

                                <label class="mt-3 block">
                                    <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Catatan Item</span>
                                    <textarea rows="2" wire:model.defer="items.{{ $index }}.notes" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Split Payment</h2>
                        <button type="button" wire:click="addPayment" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Tambah Payment</button>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($payments as $index => $payment)
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70" wire:key="pos-payment-{{ $index }}">
                                <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Metode</span>
                                        <select wire:model.defer="payments.{{ $index }}.payment_method_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">Pilih metode</option>
                                            @foreach ($paymentMethods as $method)
                                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Nominal</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="payments.{{ $index }}.amount" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Reference</span>
                                        <input type="text" wire:model.defer="payments.{{ $index }}.reference_number" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Approval</span>
                                        <input type="text" wire:model.defer="payments.{{ $index }}.approval_code" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <div class="flex items-end">
                                        <button type="button" wire:click="removePayment({{ $index }})" class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Net Total</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Rp {{ number_format($netTotal, 0, ',', '.') }}</p>
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Payment Total</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Rp {{ number_format($paymentTotal, 0, ',', '.') }}</p>
                    @if (abs($netTotal - $paymentTotal) > 0.01)
                        <p class="mt-2 text-sm font-medium text-error-700 dark:text-error-300">Total bayar harus sama dengan total belanja sebelum transaksi diposting.</p>
                    @endif
                    <button type="submit" class="mt-5 inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Post Transaksi</button>

                    @if ($errors->any())
                        <div class="mt-3 space-y-1 text-xs text-error-600">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </form>
</div>
