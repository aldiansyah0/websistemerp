@extends('layouts.app')

@php
    $statusClasses = [
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'submitted' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'approved' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'rejected' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} - Snapshot {{ $generatedAt }}</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
            <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Input Rekonsiliasi Kas</h2>
                <form method="POST" action="{{ $storeUrl }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Lokasi</span>
                        <select name="location_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            <option value="">Lokasi user aktif</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected((string) old('location_id') === (string) $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Tanggal</span>
                        <input type="date" name="reconciliation_date" value="{{ old('reconciliation_date', $defaultDate) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Opening Balance</span>
                        <input type="number" name="opening_balance" step="0.01" value="{{ old('opening_balance', 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Counted Ending</span>
                        <input type="number" name="counted_ending_balance" step="0.01" value="{{ old('counted_ending_balance', 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Catatan</span>
                        <textarea name="notes" rows="2" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">
                            Simpan Draft Rekonsiliasi
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Background Export</h2>
                <form method="POST" action="{{ $queueExportUrl }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="format" value="excel">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Start Date</span>
                        <input type="date" name="start_date" value="{{ old('start_date', $defaultDate) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">End Date</span>
                        <input type="date" name="end_date" value="{{ old('end_date', $defaultDate) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Format</span>
                        <select name="format" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            <option value="excel">Excel (CSV)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex items-center rounded-full border border-blue-light-200 px-5 py-2.5 text-sm font-semibold text-blue-light-700 transition hover:bg-blue-light-50 dark:border-blue-light-500/20 dark:text-blue-light-300">
                            Queue Export
                        </button>
                    </div>
                </form>
                <div class="mt-5 space-y-2">
                    @forelse ($reportExports as $export)
                        <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 text-sm dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="font-semibold text-gray-900 dark:text-white">Job #{{ $export['id'] }} - {{ $export['format'] }} - {{ ucfirst($export['status']) }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $export['file_name'] }}</p>
                            @if ($export['download_url'])
                                <a href="{{ $export['download_url'] }}" class="mt-2 inline-flex text-xs font-semibold text-blue-light-700 dark:text-blue-light-300">Download file</a>
                            @endif
                            @if ($export['error_message'])
                                <p class="mt-2 text-xs text-error-700 dark:text-error-300">{{ $export['error_message'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada job export.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Tanggal</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Expected</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Counted</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Selisih</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $row['date'] }}</td>
                                <td class="px-5 py-4 align-top">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $row['expected_ending'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">In {{ $row['expected_inflows'] }} / Out {{ $row['expected_outflows'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $row['counted_ending'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $row['difference_amount'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$row['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($row['can_submit'])
                                            <form method="POST" action="{{ $row['submit_url'] }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full border border-blue-light-200 px-3 py-1.5 text-xs font-semibold text-blue-light-700 transition hover:bg-blue-light-50 dark:border-blue-light-500/20 dark:text-blue-light-300">
                                                    Submit
                                                </button>
                                            </form>
                                        @endif
                                        @if ($row['can_approve'])
                                            <form method="POST" action="{{ $row['approve_url'] }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                        @if ($row['can_reject'])
                                            <form method="POST" action="{{ $row['reject_url'] }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full border border-error-200 px-3 py-1.5 text-xs font-semibold text-error-700 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-300">
                                                    Reject
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada data rekonsiliasi kas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
