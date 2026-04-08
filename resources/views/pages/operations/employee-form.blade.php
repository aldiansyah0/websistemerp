@extends('layouts.app')

@php
    $employeeModel = $employee ?? null;
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.15),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.14),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.12),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(14,165,233,0.1),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ $backUrl }}" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            Kembali ke Modul HR
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ $submitUrl }}" class="space-y-6">
            @csrf
            @if ($submitMethod !== 'POST')
                @method($submitMethod)
            @endif

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)]">
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                            Profil Karyawan
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kode Karyawan</span>
                                <input type="text" name="employee_code" value="{{ old('employee_code', $employeeModel?->employee_code) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Lengkap</span>
                                <input type="text" name="full_name" value="{{ old('full_name', $employeeModel?->full_name) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Email</span>
                                <input type="email" name="email" value="{{ old('email', $employeeModel?->email) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Telepon</span>
                                <input type="text" name="phone" value="{{ old('phone', $employeeModel?->phone) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Location (Outlet / Gudang)</span>
                                <select name="location_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Head Office / Tidak dipetakan</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" @selected((string) old('location_id', $employeeModel?->location_id) === (string) $location->id)>
                                            {{ strtoupper($location->type === 'warehouse' ? 'GDG' : 'OTL') }} - {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Outlet</span>
                                <select name="outlet_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Head Office</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" @selected((string) old('outlet_id', $employeeModel?->outlet_id) === (string) $outlet->id)>{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Department</span>
                                <select name="department" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    @foreach ($departmentOptions as $department)
                                        <option value="{{ $department }}" @selected(old('department', $employeeModel?->department) === $department)>{{ $department }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Jabatan</span>
                                <input type="text" name="position_title" value="{{ old('position_title', $employeeModel?->position_title) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Join Date</span>
                                <input type="date" name="join_date" value="{{ old('join_date', $employeeModel?->join_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                            Kompensasi dan Status
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Employment Type</span>
                                <select name="employment_type" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    @foreach ($employmentTypeOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('employment_type', $employeeModel?->employment_type ?? 'permanent') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status</span>
                                <select name="status" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $employeeModel?->status ?? 'active') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Base Salary</span>
                                <input type="number" step="0.01" min="0" name="base_salary" value="{{ old('base_salary', $employeeModel?->base_salary ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Overtime Rate</span>
                                <input type="number" step="0.01" min="0" name="overtime_rate" value="{{ old('overtime_rate', $employeeModel?->overtime_rate ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Sales Bonus Rate (%)</span>
                                <input type="number" step="0.001" min="0" max="100" name="sales_bonus_rate" value="{{ old('sales_bonus_rate', $employeeModel?->sales_bonus_rate ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Penalty Telat / Menit</span>
                                <input type="number" step="0.01" min="0" name="late_penalty_per_minute" value="{{ old('late_penalty_per_minute', $employeeModel?->late_penalty_per_minute ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Penalty Absen / Shift</span>
                                <input type="number" step="0.01" min="0" name="absence_penalty_amount" value="{{ old('absence_penalty_amount', $employeeModel?->absence_penalty_amount ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Emergency Contact</span>
                                <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $employeeModel?->emergency_contact) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan</span>
                                <textarea name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('notes', $employeeModel?->notes) }}</textarea>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                            Governance
                        </span>

                        <div class="mt-6 space-y-4">
                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Checklist HR retail</p>
                                <ul class="mt-3 space-y-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    <li>Karyawan wajib terhubung ke location outlet/gudang agar scope data dan operasional harian tetap akurat.</li>
                                    <li>Sales bonus rate serta penalti absensi dipakai langsung oleh payroll otomatis setiap periode.</li>
                                    <li>Status kerja harus akurat karena berpengaruh ke roster, payroll, dan KPI headcount aktif.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Finalisasi</p>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Simpan perubahan untuk memperbarui struktur tim yang dipakai absensi, payroll, outlet staffing, dan dashboard HR.</p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Simpan Karyawan
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
