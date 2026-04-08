@extends('layouts.app')

@php
    $workflowClasses = [
        'Scheduled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'Checked in' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300',
        'Checked out' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'Closed' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'Cancelled' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_32%),radial-gradient(circle_at_90%_20%,_rgba(249,115,22,0.14),_transparent_28%),linear-gradient(135deg,rgba(15,23,42,0.03),transparent_62%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_32%),radial-gradient(circle_at_90%_20%,_rgba(249,115,22,0.12),_transparent_28%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_62%)]"></div>
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

                        <form method="POST" action="{{ $assignUrl }}" class="mt-6 grid gap-3 rounded-3xl border border-gray-200 bg-white/90 p-4 md:grid-cols-5 dark:border-gray-800 dark:bg-gray-900/80">
                            @csrf
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Tanggal</span>
                                <input type="date" name="shift_date" value="{{ old('shift_date', $focusDate) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Karyawan</span>
                                <select name="employee_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih karyawan</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee['id'] }}">{{ $employee['employee_code'] }} - {{ $employee['full_name'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Shift</span>
                                <select name="shift_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih shift</option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift['id'] }}">{{ $shift['code'] }} / {{ $shift['name'] }} ({{ $shift['window'] }})</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Lokasi</span>
                                <select name="location_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Ikuti mapping karyawan</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location['id'] }}">{{ $location['type'] }} - {{ $location['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                    Jadwalkan Shift
                                </button>
                            </div>
                        </form>
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

        <section class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Karyawan</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Lokasi</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Shift</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Jadwal</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Clock</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                            @foreach ($assignments as $assignment)
                                <tr>
                                    <td class="px-5 py-4 align-top text-sm font-semibold text-gray-900 dark:text-white">{{ $assignment['employee'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        <p>{{ $assignment['location'] }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $assignment['location_type'] }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $assignment['shift'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $assignment['schedule'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $assignment['clock'] }}</td>
                                    <td class="px-5 py-4 align-top">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $workflowClasses[$assignment['workflow_status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $assignment['workflow_status'] }}
                                        </span>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $assignment['attendance_status'] }} / telat {{ $assignment['late_minutes'] }} / OT {{ $assignment['overtime_minutes'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($assignment['can_clock_in'])
                                                <form method="POST" action="{{ $assignment['clock_in_url'] }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-full border border-blue-light-200 px-3 py-1.5 text-xs font-semibold text-blue-light-700 transition hover:bg-blue-light-50 dark:border-blue-light-500/20 dark:text-blue-light-300 dark:hover:bg-blue-light-500/10">
                                                        Clock In
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($assignment['can_clock_out'])
                                                <form method="POST" action="{{ $assignment['clock_out_url'] }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300 dark:hover:bg-success-500/10">
                                                        Clock Out
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($assignment['can_mark_absent'])
                                                <form method="POST" action="{{ $assignment['mark_absent_url'] }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-full border border-error-200 px-3 py-1.5 text-xs font-semibold text-error-700 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-300 dark:hover:bg-error-500/10">
                                                        Mark Absent
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
