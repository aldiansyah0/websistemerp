@extends('layouts.app')

@php
    $customerModel = $customer ?? null;
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.18),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.14),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.12),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.1),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                        Kembali ke Customer
                    </a>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}">
            @csrf
            @if ($submitMethod !== 'POST')
                @method($submitMethod)
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Code</span>
                            <input type="text" name="code" value="{{ old('code', $customerModel?->code) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Customer</span>
                            <input type="text" name="name" value="{{ old('name', $customerModel?->name) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Segment</span>
                            <select name="segment" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                @foreach ($segmentOptions as $option)
                                    <option value="{{ $option }}" @selected(old('segment', $customerModel?->segment ?? 'Retail') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status</span>
                            <select name="status" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $customerModel?->status ?? 'active') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Email</span>
                            <input type="email" name="email" value="{{ old('email', $customerModel?->email) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Telepon</span>
                            <input type="text" name="phone" value="{{ old('phone', $customerModel?->phone) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kota</span>
                            <input type="text" name="city" value="{{ old('city', $customerModel?->city) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Payment Term</span>
                            <input type="number" min="0" name="payment_term_days" value="{{ old('payment_term_days', $customerModel?->payment_term_days ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Credit Limit</span>
                            <input type="number" min="0" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customerModel?->credit_limit ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Alamat</span>
                            <textarea name="address" rows="4" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('address', $customerModel?->address) }}</textarea>
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                            <textarea name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes', $customerModel?->notes) }}</textarea>
                        </label>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Panduan</p>
                        <div class="mt-4 space-y-3 text-sm leading-6 text-gray-500 dark:text-gray-400">
                            <p>Customer dengan payment term dan credit limit yang rapi akan membuat invoice, AR aging, dan cashflow lebih presisi.</p>
                            <p>Gunakan segment untuk memisahkan akun retail, corporate, membership, atau wholesale agar analitik sales lebih tajam.</p>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            Simpan Customer
                        </button>
                    </div>
                </div>
            </section>
        </form>
    </div>
@endsection
