@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.18),_transparent_30%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.14),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.14),_transparent_30%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.1),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
            <div class="relative px-6 py-7 md:px-8 md:py-8">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.9fr)]">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                            <span>{{ $pageEyebrow }}</span>
                            <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Snapshot {{ $generatedAt }}</span>
                        </div>
                        <h1 class="mt-5 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">{{ $pageTitle }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">{{ $pageDescription }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($heroStats as $stat)
                            <div class="rounded-[26px] border border-white/80 bg-white/85 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-gray-900/75">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-400">{{ $stat['label'] }}</p>
                                <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $stat['caption'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-400">{{ $metric['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $metric['value'] }}</p>
                    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $metric['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <div class="space-y-6">
                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                        Accounts Receivable
                    </span>
                    <div class="mt-6 overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Invoice</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Customer</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Due</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Outstanding</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                    @forelse ($receivables as $item)
                                        <tr>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['invoice_number'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['outlet'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item['customer'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['payments'] ?: 'Belum ada pembayaran' }}</p>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item['due_date'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['invoice_date'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['balance_due'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paid {{ $item['paid_amount'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item['status'] }}</td>
                                            <td class="px-5 py-4">
                                                <a href="{{ $item['payment_url'] }}" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300 dark:hover:bg-success-500/10">
                                                    Terima
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada invoice receivable yang terbuka.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                        Accounts Payable
                    </span>
                    <div class="mt-6 overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">PO</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Due</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Outstanding</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                    @forelse ($payables as $item)
                                        <tr>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['po_number'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['warehouse'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item['supplier'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['payments'] ?: 'Belum ada pembayaran' }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item['due_date'] }}</td>
                                            <td class="px-5 py-4 align-top">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['balance_due'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paid {{ $item['paid_amount'] }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item['status'] }}</td>
                                            <td class="px-5 py-4">
                                                <a href="{{ $item['payment_url'] }}" class="inline-flex items-center rounded-full border border-orange-200 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-50 dark:border-orange-500/20 dark:text-orange-300 dark:hover:bg-orange-500/10">
                                                    Bayar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada hutang supplier yang terbuka.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                    Action Focus
                </span>
                <div class="mt-6 space-y-3">
                    @foreach ($actionCards as $card)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $card['title'] }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $card['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
