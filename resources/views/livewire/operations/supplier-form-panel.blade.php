<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Kembali ke Supplier</a>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kode</span>
                            <input type="text" wire:model.defer="code" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('code') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Supplier</span>
                            <input type="text" wire:model.defer="name" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('name') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Contact Person</span>
                            <input type="text" wire:model.defer="contact_person" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Email</span>
                            <input type="email" wire:model.defer="email" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            @error('email') <p class="mt-1 text-xs text-error-600">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Telepon</span>
                            <input type="text" wire:model.defer="phone" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kota</span>
                            <input type="text" wire:model.defer="city" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Alamat</span>
                            <textarea rows="3" wire:model.defer="address" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        </label>
                    </div>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Lead Time (hari)</span>
                            <input type="number" min="0" wire:model.defer="lead_time_days" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Payment Term (hari)</span>
                            <input type="number" min="0" wire:model.defer="payment_term_days" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Fill Rate (%)</span>
                            <input type="number" min="0" max="100" step="0.01" wire:model.defer="fill_rate" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reject Rate (%)</span>
                            <input type="number" min="0" max="100" step="0.01" wire:model.defer="reject_rate" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Rating (0-5)</span>
                            <input type="number" min="0" max="5" step="0.01" wire:model.defer="rating" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                            <textarea rows="3" wire:model.defer="notes" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-white"></textarea>
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <input type="checkbox" wire:model.defer="is_active" class="mt-1 h-4 w-4 rounded border-gray-300 text-gray-900">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">Supplier Aktif</span>
                            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">Supplier nonaktif tidak tampil pada workflow PO baru.</span>
                        </span>
                    </label>
                </div>

                <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Simpan Supplier</button>
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
