@extends('layouts.app')

@php
    $statusClasses = [
        'closed' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
        'open' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} • Snapshot {{ $generatedAt }}</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
        </section>

        <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Tutup Periode</h2>
            <form method="POST" action="{{ $closeUrl }}" class="mt-5 grid gap-4 md:grid-cols-4">
                @csrf
                <label class="block">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Period Code</span>
                    <input type="text" name="period_code" value="{{ old('period_code', $defaultPeriodCode) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Start Date</span>
                    <input type="date" name="start_date" value="{{ old('start_date', $defaultStartDate) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">End Date</span>
                    <input type="date" name="end_date" value="{{ old('end_date', $defaultEndDate) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                </label>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">
                        Close Period
                    </button>
                </div>
                <label class="block md:col-span-4">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Catatan</span>
                    <textarea name="notes" rows="2" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                </label>
            </form>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Periode</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Range</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Closed By</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($periods as $period)
                            <tr>
                                <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $period['period_code'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $period['range'] }}</td>
                                <td class="px-5 py-4 align-top">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $period['closed_by'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $period['closed_at'] ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$period['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $period['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($period['can_reopen'])
                                        <form method="POST" action="{{ $period['reopen_url'] }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300">
                                                Reopen
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Active</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada periode closing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

