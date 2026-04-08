@extends('layouts.app')

@php
    $statusClasses = [
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'pending_approval' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'approved' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'partially_received' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300',
        'received' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'rejected' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
        'cancelled' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300',
    ];
    $paymentStatusClasses = [
        'Unpaid' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
        'Partial' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'Paid' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.18),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(34,197,94,0.16),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.14),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(34,197,94,0.12),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
            <div class="relative px-6 py-7 md:px-8 md:py-8">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.9fr)]">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                            <span>{{ $pageEyebrow }}</span>
                            <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Snapshot {{ $generatedAt }}</span>
                        </div>
                        <h1 class="mt-5 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">{{ $pageTitle }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">
                            {{ $pageDescription }}
                            Workflow PO sekarang hidup dari draft, submit approval, approve, reject, sampai cancel langsung dari data riil di database.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Buat Purchase Order
                            </a>
                        </div>
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

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($metrics as $metric)
                <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-400">{{ $metric['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $metric['value'] }}</p>
                    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $metric['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.35fr)_minmax(0,0.65fr)]">
            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                            Live Workflow
                        </span>
                        <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            Approval queue dan eksekusi buyer sekarang berjalan dari database
                        </h2>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">PO</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Buyer / Lokasi</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Amount</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">ETA</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                @foreach ($purchaseOrders as $po)
                                    <tr>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $po['po_number'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $po['items_count'] }} item</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['supplier'] }}</td>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $po['buyer'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $po['warehouse'] }}</p>
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $po['total_amount'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Order {{ $po['order_date'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paid {{ $po['paid_amount'] }} / Outstanding {{ $po['balance_due'] }}</p>
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $po['expected_date'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Due {{ $po['due_date'] }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$po['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $po['status_label'] }}
                                            </span>
                                            <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $paymentStatusClasses[$po['payment_status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $po['payment_status'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                @if ($po['can_edit'])
                                                    <a href="{{ $po['edit_url'] }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                                        Edit
                                                    </a>
                                                @endif

                                                @if ($po['can_submit'])
                                                    <form method="POST" action="{{ $po['submit_url'] }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-blue-light-200 px-3 py-1.5 text-xs font-semibold text-blue-light-700 transition hover:bg-blue-light-50 dark:border-blue-light-500/20 dark:text-blue-light-300 dark:hover:bg-blue-light-500/10">
                                                            Submit
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($po['can_approve'])
                                                    <form method="POST" action="{{ $po['approve_url'] }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300 dark:hover:bg-success-500/10">
                                                            Approve
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($po['can_reject'])
                                                    <form method="POST" action="{{ $po['reject_url'] }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-error-200 px-3 py-1.5 text-xs font-semibold text-error-700 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-300 dark:hover:bg-error-500/10">
                                                            Reject
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($po['can_cancel'])
                                                    <form method="POST" action="{{ $po['cancel_url'] }}" onsubmit="return confirm('Batalkan purchase order ini?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-orange-200 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-50 dark:border-orange-500/20 dark:text-orange-300 dark:hover:bg-orange-500/10">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($po['can_receive'])
                                                    <a href="{{ $po['receive_url'] }}" class="inline-flex items-center rounded-full border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50 dark:border-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/10">
                                                        Receive
                                                    </a>
                                                @endif

                                                @if ($po['can_pay'])
                                                    <a href="{{ $po['payment_url'] }}" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300 dark:hover:bg-success-500/10">
                                                        Bayar
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                    Pipeline
                </span>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Tahap yang paling menentukan kecepatan suplai
                </h2>
                <div class="mt-6 space-y-3">
                    @foreach ($pipelineCards as $card)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $card['title'] }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $card['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                    Spend Mix
                </span>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Belanja aktif harus mengikuti kategori yang paling butuh suplai
                </h2>
                <div class="mt-6 space-y-4">
                    @forelse ($spendMix as $item)
                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['value'] }}</p>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-gradient-to-r from-blue-light-500 to-success-400" style="width: {{ $item['share'] }}%;"></div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $item['share'] }}% dari committed spend</p>
                        </div>
                    @empty
                        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">Belum ada spend terbuka yang bisa dipetakan per kategori.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                    Action Queue
                </span>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Tiga keputusan procurement yang paling berdampak hari ini
                </h2>
                <div class="mt-6 space-y-3">
                    @foreach ($actionQueue as $action)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $action['title'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $action['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
