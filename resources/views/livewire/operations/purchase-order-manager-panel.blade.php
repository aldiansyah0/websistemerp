<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
            </div>
            <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Buat PO</a>
        </div>
        @error('workflow')
            <p class="mt-3 text-sm font-medium text-error-700 dark:text-error-300">{{ $message }}</p>
        @enderror
    </section>

    <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">PO</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Amount</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($purchaseOrders as $po)
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $po['po_number'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $po['order_date'] }} - {{ $po['warehouse'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['supplier'] }}</td>
                            <td class="px-5 py-4 align-top">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $po['total_amount'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Outstanding {{ $po['balance_due'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['status_label'] }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @if ($po['can_edit'])
                                        <a href="{{ $po['edit_url'] }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">Edit</a>
                                    @endif
                                    @if ($po['can_submit'])
                                        <button
                                            type="button"
                                            wire:click="submitPurchaseOrder({{ $po['id'] }})"
                                            class="inline-flex items-center rounded-full border border-blue-light-200 px-3 py-1.5 text-xs font-semibold text-blue-light-700 dark:border-blue-light-500/20 dark:text-blue-light-300"
                                        >
                                            Submit
                                        </button>
                                    @endif
                                    @if ($po['can_approve'])
                                        <button
                                            type="button"
                                            wire:click="approvePurchaseOrder({{ $po['id'] }})"
                                            class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 dark:border-success-500/20 dark:text-success-300"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="rejectPurchaseOrder({{ $po['id'] }})"
                                            class="inline-flex items-center rounded-full border border-error-200 px-3 py-1.5 text-xs font-semibold text-error-700 dark:border-error-500/20 dark:text-error-300"
                                        >
                                            Reject
                                        </button>
                                    @endif
                                    @if ($po['can_cancel'])
                                        <button
                                            type="button"
                                            wire:click="cancelPurchaseOrder({{ $po['id'] }})"
                                            class="inline-flex items-center rounded-full border border-warning-200 px-3 py-1.5 text-xs font-semibold text-warning-700 dark:border-warning-500/20 dark:text-warning-300"
                                        >
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada purchase order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
