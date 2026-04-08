@extends('layouts.app')

@php
    $supplierModel = $supplier ?? null;
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.18),_transparent_30%),radial-gradient(circle_at_85%_18%,_rgba(56,189,248,0.14),_transparent_24%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.14),_transparent_30%),radial-gradient(circle_at_85%_18%,_rgba(56,189,248,0.1),_transparent_24%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                        Kembali ke Supplier
                    </a>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6">
            @csrf
            @if ($submitMethod !== 'POST')
                @method($submitMethod)
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)]">
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                            Data Supplier
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kode Supplier</span>
                                <input type="text" name="code" value="{{ old('code', $supplierModel?->code) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Supplier</span>
                                <input type="text" name="name" value="{{ old('name', $supplierModel?->name) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Contact Person</span>
                                <input type="text" name="contact_person" value="{{ old('contact_person', $supplierModel?->contact_person) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Email</span>
                                <input type="email" name="email" value="{{ old('email', $supplierModel?->email) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Telepon</span>
                                <input type="text" name="phone" value="{{ old('phone', $supplierModel?->phone) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kota</span>
                                <input type="text" name="city" value="{{ old('city', $supplierModel?->city) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Alamat</span>
                                <textarea name="address" rows="4" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('address', $supplierModel?->address) }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                            SLA dan Finansial
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Lead Time (hari)</span>
                                <input type="number" min="0" name="lead_time_days" value="{{ old('lead_time_days', $supplierModel?->lead_time_days ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Payment Term (hari)</span>
                                <input type="number" min="0" name="payment_term_days" value="{{ old('payment_term_days', $supplierModel?->payment_term_days ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Fill Rate (%)</span>
                                <input type="number" min="0" max="100" step="0.01" name="fill_rate" value="{{ old('fill_rate', $supplierModel?->fill_rate ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Reject Rate (%)</span>
                                <input type="number" min="0" max="100" step="0.01" name="reject_rate" value="{{ old('reject_rate', $supplierModel?->reject_rate ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Rating (0-5)</span>
                                <input type="number" min="0" max="5" step="0.01" name="rating" value="{{ old('rating', $supplierModel?->rating ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                                <textarea name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes', $supplierModel?->notes) }}</textarea>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                            Status Supplier
                        </span>
                        <div class="mt-6">
                            <label class="flex items-start gap-3 rounded-[24px] border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplierModel?->is_active ?? true)) class="mt-1 h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">Supplier Aktif</span>
                                    <span class="mt-1 block text-sm leading-6 text-gray-500 dark:text-gray-400">Nonaktifkan supplier bila kontrak berhenti agar tidak muncul di form pembelian baru.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Finalisasi</p>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Simpan profil supplier untuk memperbarui directory procurement, evaluasi SLA, dan pembayaran hutang.</p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Simpan Supplier
                            </button>
                            <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection
