<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Kembali ke Master Produk</a>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">SKU</span>
                            <input type="text" wire:model.defer="sku" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('sku') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Barcode</span>
                            <input type="text" wire:model.defer="barcode" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('barcode') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Produk</span>
                            <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('name') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kategori</span>
                            <select wire:model.defer="category_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <a href="{{ $createCategoryUrl }}" class="mt-2 inline-flex text-xs font-semibold text-blue-light-700 dark:text-blue-light-300">+ Tambah kategori</a>
                            @error('category_id') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Supplier Utama</span>
                            <select wire:model.defer="primary_supplier_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                <option value="">Belum dipilih</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <a href="{{ $createSupplierUrl }}" class="mt-2 inline-flex text-xs font-semibold text-blue-light-700 dark:text-blue-light-300">+ Tambah supplier</a>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Satuan</span>
                            <select wire:model.defer="unit_of_measure" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                @foreach ($unitOptions as $unit)
                                    <option value="{{ $unit }}">{{ strtoupper($unit) }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status</span>
                            <select wire:model.defer="status" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Deskripsi</span>
                            <textarea rows="4" wire:model.defer="description" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        </label>
                    </div>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Cost Price</span>
                            <input type="number" min="0" step="0.01" wire:model.defer="cost_price" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('cost_price') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Selling Price</span>
                            <input type="number" min="0" step="0.01" wire:model.defer="selling_price" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('selling_price') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Daily Run Rate</span>
                            <input type="number" min="0" step="0.01" wire:model.defer="daily_run_rate" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Shelf Life (hari)</span>
                            <input type="number" min="0" wire:model.defer="shelf_life_days" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reorder Level</span>
                            <input type="number" min="0" step="0.01" wire:model.defer="reorder_level" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reorder Quantity</span>
                            <input type="number" min="0" step="0.01" wire:model.defer="reorder_quantity" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <input type="checkbox" wire:model.defer="is_featured" class="mt-1 h-4 w-4 rounded border-gray-300 text-gray-900">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">Featured SKU</span>
                            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">SKU prioritas untuk promo dan visibilitas dashboard.</span>
                        </span>
                    </label>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Simpan Produk</button>
                        <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Batal</a>
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
    </form>
</div>
