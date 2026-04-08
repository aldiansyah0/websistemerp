@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.18),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(34,197,94,0.14),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.14),_transparent_30%),radial-gradient(circle_at_88%_18%,_rgba(34,197,94,0.1),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                        Kembali ke Hutang / Piutang
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)]">
            <div class="space-y-6">
                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">PO</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ $purchaseOrder->po_number }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $purchaseOrder->supplier?->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Lokasi</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ $purchaseOrder->warehouse?->name ?? '-' }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Due {{ $purchaseOrder->due_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Total PO</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Paid Rp {{ number_format($purchaseOrder->paid_amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Saldo</p>
                            <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($purchaseOrder->balance_due, 0, ',', '.') }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ \App\Models\PurchaseOrder::paymentStatusOptions()[$purchaseOrder->payment_status] ?? ucfirst($purchaseOrder->payment_status) }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ $submitUrl }}" class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    @csrf
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nomor Invoice Supplier</span>
                            <input type="text" name="supplier_invoice_number" value="{{ old('supplier_invoice_number', $purchaseOrder->supplier_invoice_number) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Metode Pembayaran</span>
                            <select name="payment_method_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Pembayaran</span>
                            <input type="datetime-local" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d\\TH:i')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nominal</span>
                            <input type="number" min="0" step="0.01" name="amount" value="{{ old('amount', $purchaseOrder->balance_due) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reference</span>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                            <textarea name="notes" rows="3" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                        </label>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            Simpan Pembayaran Supplier
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                    Histori Pembayaran
                </span>
                <div class="mt-6 space-y-3">
                    @forelse ($purchaseOrder->payments as $payment)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $payment->paymentMethod?->name ?? '-' }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $payment->payment_date?->format('d M Y H:i') ?? '-' }} / {{ $payment->reference_number ?: 'Tanpa referensi' }}</p>
                        </div>
                    @empty
                        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">Belum ada pembayaran supplier yang tercatat untuk PO ini.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
