@extends('layouts.app')

@php
    $statusClasses = [
        'Hero' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'Stable' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300',
        'Watch' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'Replenish' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300',
        'Critical' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
        'Inactive' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'Discontinued' => 'bg-gray-900 text-white dark:bg-white dark:text-gray-900',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.18),_transparent_30%),radial-gradient(circle_at_85%_18%,_rgba(249,115,22,0.16),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.14),_transparent_30%),radial-gradient(circle_at_85%_18%,_rgba(249,115,22,0.12),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
            <div class="relative px-6 py-7 md:px-8 md:py-8">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.9fr)]">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                            <span>{{ $pageEyebrow }}</span>
                            <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Snapshot {{ $generatedAt }}</span>
                        </div>
                        <h1 class="mt-5 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">
                            {{ $pageTitle }}
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">
                            {{ $pageDescription }}
                            Halaman ini menjadi fondasi untuk pricing, assortment, replenishment, dan sinkronisasi ke POS,
                            stok, supplier, serta purchase order.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <div class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                Data master yang rapi akan mengurangi selisih stok, salah harga, dan pembelian yang tidak presisi
                            </div>
                            <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Tambah Produk
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

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                            Category Mix
                        </span>
                        <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            Assortment harus dibaca lewat kontribusi kategori dan kualitas margin
                        </h2>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($categoryMix as $category)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category['name'] }}</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $category['note'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($category['share'], 1, ',', '.') }}%</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">share stock value</p>
                                </div>
                            </div>
                            <div class="mt-4 h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-gradient-to-r from-blue-light-500 to-orange-400" style="width: {{ $category['share'] }}%;"></div>
                            </div>
                            <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-300">Margin {{ number_format($category['margin'], 1, ',', '.') }}%</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                    Data Governance
                </span>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Master produk yang baik adalah kontrak data lintas modul
                </h2>
                <div class="mt-6 grid gap-3">
                    @foreach ($governanceCards as $card)
                        <div class="rounded-[22px] border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $card['title'] }}</p>
                            <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $card['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.35fr)_minmax(0,0.65fr)]">
            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                            Product Priority
                        </span>
                        <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            SKU prioritas yang paling memengaruhi penjualan, stok, dan pembelian
                        </h2>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">SKU</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Produk</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Harga</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Stok</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Cover</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Margin</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $product['sku'] }}</td>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $product['category'] }}@if($product['barcode']) • {{ $product['barcode'] }}@endif
                                            </p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $product['supplier'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($product['price'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($product['current_stock'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $product['stock_days'] !== null ? number_format($product['stock_days'], 1, ',', '.') . ' hari' : '-' }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($product['margin'], 1, ',', '.') }}%</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$product['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $product['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ $product['edit_url'] }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('products.destroy', $product['id']) }}" onsubmit="return confirm('Hapus produk ini dari master data?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center rounded-full border border-error-200 px-3 py-1.5 text-xs font-semibold text-error-700 transition hover:bg-error-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-error-500/20 dark:text-error-300 dark:hover:bg-error-500/10" @disabled($product['delete_blocked'])>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                            @if ($product['delete_blocked'])
                                                <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">Sudah dipakai di ledger atau purchase order.</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                    Watchlist
                </span>
                <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Pekerjaan yang perlu ditutup agar katalog siap dipakai ERP
                </h2>
                <div class="mt-6 space-y-3">
                    @foreach ($watchlist as $item)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
