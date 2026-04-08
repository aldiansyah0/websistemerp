@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.18),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(14,165,233,0.14),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.14),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(14,165,233,0.1),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
            <div class="relative px-6 py-7 md:px-8 md:py-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                            <span>{{ $pageEyebrow }}</span>
                            <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Snapshot {{ $generatedAt }}</span>
                        </div>
                        <h1 class="mt-5 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">{{ $pageTitle }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">{{ $pageDescription }}</p>
                    </div>
                    <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Kembali ke Mutasi
                    </a>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6">
            @csrf
            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)]">
                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-6 grid gap-4 md:grid-cols-2">
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Transfer</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ $stockTransfer->transfer_number }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $stockTransfer->sourceWarehouse?->name ?? '-' }} -> {{ $stockTransfer->destinationWarehouse?->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Requested</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($stockTransfer->total_quantity, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">ETA {{ $stockTransfer->expected_receipt_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($stockTransfer->items as $index => $item)
                            @php
                                $outstanding = max((float) $item->requested_quantity - (float) $item->received_quantity, 0);
                            @endphp
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <input type="hidden" name="items[{{ $index }}][stock_transfer_item_id]" value="{{ $item->id }}">
                                <div class="grid gap-4 md:grid-cols-[minmax(0,1.2fr)_repeat(3,minmax(0,0.7fr))]">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->product?->name ?? '-' }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->product?->sku ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Requested</p>
                                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->requested_quantity, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Received</p>
                                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->received_quantity, 0, ',', '.') }}</p>
                                    </div>
                                    <label class="block">
                                        <span class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">Terima Sekarang</span>
                                        <input type="number" min="0" max="{{ $outstanding }}" step="0.01" name="items[{{ $index }}][received_quantity]" value="{{ old('items.' . $index . '.received_quantity', $outstanding) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan Receiving</span>
                            <textarea name="notes" rows="6" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            Post Receiving Transfer
                        </button>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection
