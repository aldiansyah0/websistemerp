<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Kembali ke Kategori</a>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kode</span>
                        <input type="text" wire:model.defer="code" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        @error('code') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Urutan</span>
                        <input type="number" min="0" wire:model.defer="sort_order" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        @error('sort_order') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Kategori</span>
                        <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        @error('name') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Deskripsi</span>
                        <textarea rows="5" wire:model.defer="description" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        @error('description') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                    </label>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <input type="checkbox" wire:model.defer="is_active" class="mt-1 h-4 w-4 rounded border-gray-300 text-gray-900">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">Kategori Aktif</span>
                            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">Jika nonaktif, kategori tidak muncul pada input master produk baru.</span>
                        </span>
                    </label>
                    @error('is_active') <p class="mt-2 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Simpan Kategori</button>
                        <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Batal</a>
                    </div>
                    @error('workflow') <p class="mt-2 text-xs text-error-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
    </form>
</div>
