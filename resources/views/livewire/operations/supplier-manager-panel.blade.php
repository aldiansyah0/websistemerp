<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Tambah Supplier</a>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($metrics as $metric)
            <div class="rounded-[24px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $metric['label'] }}</p>
                <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $metric['value'] }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $metric['note'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Fill Rate</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Lead Time</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $supplier['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $supplier['code'] }} - {{ $supplier['contact'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $supplier['fill_rate'] }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $supplier['lead_time'] }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $supplier['status'] }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $supplier['edit_url'] }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Edit</a>
                                    @if ($supplier['can_delete'])
                                        <form method="POST" action="{{ $supplier['delete_url'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-full border border-error-200 px-3 py-1.5 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
