<div class="space-y-6">
    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Livewire - {{ $generatedAt }}</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Latest event: {{ $latestAuditAt }}</p>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[24px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Total Logs</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($summary['total'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[24px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Today</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($summary['today'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[24px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Approval Actions</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($summary['approvals'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[24px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Failure Logs</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-error-700 dark:text-error-300">{{ number_format($summary['failures'], 0, ',', '.') }}</p>
        </div>
    </section>

    <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <label class="block xl:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Module</span>
                <select wire:model.live="module" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    <option value="">Semua</option>
                    @foreach ($moduleOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block xl:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Action</span>
                <select wire:model.live="action" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    <option value="">Semua</option>
                    @foreach ($actionOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block xl:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">User</span>
                <select wire:model.live="user_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    <option value="">Semua</option>
                    @foreach ($userOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block xl:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Date From</span>
                <input type="date" wire:model.live="date_from" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
            </label>
            <label class="block xl:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Date To</span>
                <input type="date" wire:model.live="date_to" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
            </label>
            <label class="block xl:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Rows</span>
                <select wire:model.live="perPage" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </label>
            <label class="block md:col-span-2 xl:col-span-5">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Search</span>
                <input type="text" wire:model.live.debounce.350ms="search" placeholder="Cari module/action/event/auditable/user" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
            </label>
            <div class="flex items-end xl:col-span-1">
                <button type="button" wire:click="clearFilters" class="inline-flex items-center rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-300">
                    Reset Filter
                </button>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Waktu</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Module</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Action</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Event</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">User</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                                <p>{{ $log->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i:s') ?? '-' }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $log->ip_address ?: '-' }}</p>
                            </td>
                            <td class="px-5 py-4 align-top text-sm font-semibold text-gray-900 dark:text-white">{{ $log->module }}</td>
                            <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300">{{ $log->action }}</td>
                            <td class="px-5 py-4 align-top">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $log->event }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $log->auditable_type ?? '-' }} {{ $log->auditable_id ? '#' . $log->auditable_id : '' }}</p>
                            </td>
                            <td class="px-5 py-4 align-top text-sm text-gray-700 dark:text-gray-300">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if (! empty($log->metadata))
                                    <details class="rounded-2xl border border-gray-200 bg-gray-50/80 p-3 text-xs dark:border-gray-800 dark:bg-gray-900/70">
                                        <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300">Lihat metadata</summary>
                                        <pre class="mt-2 whitespace-pre-wrap break-words text-[11px] leading-5 text-gray-600 dark:text-gray-300">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $logs->links() }}
        </div>
    </section>
</div>

