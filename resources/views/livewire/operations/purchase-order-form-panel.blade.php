<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Kembali ke Purchase Order</a>
        </div>
    </section>

    <div class="space-y-6">
        @unless ($canEdit)
            <div class="rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-200">
                Purchase order pada status ini tidak bisa diedit lagi.
            </div>
        @endunless

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Supplier</span>
                            <select wire:model.defer="supplier_id" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Lokasi Receiving</span>
                            <select wire:model.defer="warehouse_id" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih lokasi</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Order Date</span>
                            <input type="date" wire:model.defer="order_date" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('order_date') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Expected Date</span>
                            <input type="date" wire:model.defer="expected_date" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('expected_date') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Terms</span>
                            <textarea rows="2" wire:model.defer="terms" @disabled(!$canEdit) class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan Buyer</span>
                            <textarea rows="3" wire:model.defer="notes" @disabled(!$canEdit) class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        </label>
                    </div>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Line Item Purchase</h2>
                        <button type="button" wire:click="addItem" @disabled(!$canEdit) class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 disabled:opacity-50 dark:border-gray-800 dark:text-gray-300">Tambah Item</button>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($items as $index => $item)
                            @php
                                $quantity = (float) ($item['ordered_quantity'] ?? 0);
                                $cost = (float) ($item['unit_cost'] ?? 0);
                                $discount = (float) ($item['discount_amount'] ?? 0);
                                $lineTotal = max(($quantity * $cost) - $discount, 0);
                            @endphp
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70" wire:key="po-item-{{ $index }}">
                                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.7fr)_repeat(3,minmax(0,0.8fr))_minmax(0,1fr)_auto]">
                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Produk</span>
                                        <select wire:model.defer="items.{{ $index }}.product_id" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            <option value="">Pilih produk</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Qty</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="items.{{ $index }}.ordered_quantity" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Unit Cost</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="items.{{ $index }}.unit_cost" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Diskon</span>
                                        <input type="number" min="0" step="0.01" wire:model.defer="items.{{ $index }}.discount_amount" @disabled(!$canEdit) class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>

                                    <div class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Line Total</span>
                                        <div class="mt-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                            Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="flex items-end">
                                        <button type="button" wire:click="removeItem({{ $index }})" @disabled(!$canEdit) class="inline-flex items-center rounded-full border border-error-200 px-3 py-2 text-xs font-semibold text-error-700 disabled:opacity-50 dark:border-error-500/20 dark:text-error-300">Hapus</button>
                                    </div>
                                </div>

                                <label class="mt-3 block">
                                    <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Catatan Item</span>
                                    <textarea rows="2" wire:model.defer="items.{{ $index }}.notes" @disabled(!$canEdit) class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 disabled:opacity-60 dark:border-gray-800 dark:bg-gray-950 dark:text-white"></textarea>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    @php
                        $grandTotal = collect($items)->sum(function (array $item): float {
                            $quantity = (float) ($item['ordered_quantity'] ?? 0);
                            $cost = (float) ($item['unit_cost'] ?? 0);
                            $discount = (float) ($item['discount_amount'] ?? 0);
                            return max(($quantity * $cost) - $discount, 0);
                        });
                    @endphp
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Total Draft PO</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Simpan sebagai draft atau submit ke approval dari panel Livewire ini.</p>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="saveDraft" @disabled(!$canEdit) class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 disabled:opacity-50 dark:border-gray-800 dark:text-gray-300">Simpan Draft</button>
                        <button type="button" wire:click="submitForApproval" @disabled(!$canEdit) class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50 dark:bg-white dark:text-gray-900">Submit Approval</button>
                    </div>
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
    </div>
</div>
