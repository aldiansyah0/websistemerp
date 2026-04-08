@extends('layouts.app')

@php
    $statusClasses = [
        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'pending_approval' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'approved' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'rejected' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">{{ $pageEyebrow }} • Snapshot {{ $generatedAt }}</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $pageTitle }}</h1>
                    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $pageDescription }}</p>
                </div>
                <a href="{{ $createUrl }}" class="inline-flex items-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    Buat Stock Opname
                </a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($heroStats as $stat)
                <div class="rounded-[24px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-400">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $stat['caption'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Dokumen</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Gudang</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Variance</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Approver</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($opnames as $opname)
                            <tr>
                                <td class="px-5 py-4 align-top">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $opname['opname_number'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $opname['opname_date'] }} • {{ $opname['items_count'] }} item</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $opname['warehouse'] }}</td>
                                <td class="px-5 py-4 align-top">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $opname['total_variance_qty'] }} qty</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $opname['total_variance_value'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $opname['approver'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$opname['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $opname['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($opname['can_edit'])
                                            <a href="{{ $opname['edit_url'] }}" class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                                Edit
                                            </a>
                                        @endif
                                        @if ($opname['can_submit'])
                                            <form method="POST" action="{{ $opname['submit_url'] }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full border border-blue-light-200 px-3 py-1.5 text-xs font-semibold text-blue-light-700 transition hover:bg-blue-light-50 dark:border-blue-light-500/20 dark:text-blue-light-300">
                                                    Submit
                                                </button>
                                            </form>
                                        @endif
                                        @if ($opname['can_approve'])
                                            <form method="POST" action="{{ $opname['approve_url'] }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-full border border-success-200 px-3 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-50 dark:border-success-500/20 dark:text-success-300">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                        @if ($opname['can_reject'])
                                            <form method="POST" action="{{ $opname['reject_url'] }}">
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
                                <td colspan="6" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada dokumen stock opname.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

