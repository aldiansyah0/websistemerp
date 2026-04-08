<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Buat Transaksi POS</a>
        </div>
    </section>

    <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Transaksi</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Outlet</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Items</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Payment</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $transaction['transaction_number'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $transaction['sold_at'] }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $transaction['outlet'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $transaction['cashier'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $transaction['items_count'] }} unit</td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $transaction['payments'] }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $transaction['net_amount'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi POS.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
