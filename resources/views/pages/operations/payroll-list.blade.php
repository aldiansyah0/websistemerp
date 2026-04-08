@extends('layouts.app')

@php
    $statusClasses = [
        'Draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'Processing' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300',
        'Approved' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'Paid' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.16),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(249,115,22,0.14),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.12),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(249,115,22,0.1),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                        <form method="POST" action="{{ $generateUrl }}" class="mt-5 grid gap-3 rounded-3xl border border-gray-200 bg-white/90 p-4 md:grid-cols-4 dark:border-gray-800 dark:bg-gray-900/80">
                            @csrf
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Periode Mulai</span>
                                <input type="date" name="period_start" value="{{ old('period_start', $defaultPeriodStart) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Periode Selesai</span>
                                <input type="date" name="period_end" value="{{ old('period_end', $defaultPeriodEnd) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Lokasi (Opsional)</span>
                                <select name="location_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Semua Lokasi</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location['id'] }}" @selected((string) old('location_id') === (string) $location['id'])>{{ $location['type'] }} - {{ $location['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                    Generate Payroll Otomatis
                                </button>
                            </div>
                            <input type="hidden" name="notes" value="Generated otomatis dari POS + absensi shift">
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

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Payroll</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Periode</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Headcount</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Gross</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Deduction</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Net</th>
                                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                @foreach ($payrollRuns as $payrollRun)
                                    <tr>
                                        <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $payrollRun['code'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payrollRun['period'] }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$payrollRun['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $payrollRun['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payrollRun['employee_count'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payrollRun['gross'] }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payrollRun['deductions'] }}</td>
                                        <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $payrollRun['net'] }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                @if ($payrollRun['can_submit'])
                                                    <form method="POST" action="{{ $payrollRun['submit_url'] }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-blue-light-200 px-3 py-1.5 text-xs font-semibold text-blue-light-700 transition hover:bg-blue-light-50 dark:border-blue-light-500/20 dark:text-blue-light-300 dark:hover:bg-blue-light-500/10">
                                                            Submit
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($payrollRun['can_approve'])
                                                    <form method="POST" action="{{ $payrollRun['approve_url'] }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300 dark:hover:bg-success-500/10">
                                                            Approve
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($payrollRun['can_pay'])
                                                    <form method="POST" action="{{ $payrollRun['pay_url'] }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50 dark:border-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/10">
                                                            Pay
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
            </div>

            <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                    Top Earners
                </span>
                <div class="mt-6 space-y-3">
                    @foreach ($topEarners as $earner)
                        <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $earner['employee'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $earner['position'] }} / {{ $earner['outlet'] }}</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $earner['status'] }}</p>
                            </div>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $earner['net_salary'] }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Bonus POS {{ $earner['sales_bonus'] }} / Potongan absensi {{ $earner['attendance_deduction'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
