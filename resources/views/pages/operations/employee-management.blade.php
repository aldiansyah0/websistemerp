@extends('layouts.app')

@php
    $statusClasses = [
        'Active' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'Leave' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'Resigned' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.15),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.14),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.12),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.1),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Tambah Karyawan
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

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Karyawan</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Department</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Location</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Kontrak</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Bonus POS</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Base Salary</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                @foreach ($employees as $employee)
                                    <tr>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $employee['full_name'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $employee['employee_code'] }} / {{ $employee['position'] }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $employee['department'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            <p>{{ $employee['location'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $employee['location_type'] }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $employee['employment_type'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $employee['sales_bonus_rate'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $employee['base_salary'] }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$employee['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $employee['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <a href="{{ $employee['edit_url'] }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="space-y-3">
                    @foreach ($departmentCards as $card)
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
