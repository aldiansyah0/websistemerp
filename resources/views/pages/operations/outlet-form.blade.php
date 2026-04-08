@extends('layouts.app')

@php
    $outletModel = $outlet ?? null;
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(34,197,94,0.14),_transparent_26%),linear-gradient(135deg,rgba(15,23,42,0.04),transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_28%),radial-gradient(circle_at_85%_18%,_rgba(34,197,94,0.1),_transparent_26%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]"></div>
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
                            Kembali ke Direktori Outlet
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
                            Profil Outlet
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kode Outlet</span>
                                <input type="text" name="code" value="{{ old('code', $outletModel?->code) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Outlet</span>
                                <input type="text" name="name" value="{{ old('name', $outletModel?->name) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Region</span>
                                <select name="region" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Pilih region</option>
                                    @foreach ($regionOptions as $region)
                                        <option value="{{ $region }}" @selected(old('region', $outletModel?->region) === $region)>{{ $region }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kota</span>
                                <input type="text" name="city" value="{{ old('city', $outletModel?->city) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Telepon</span>
                                <input type="text" name="phone" value="{{ old('phone', $outletModel?->phone) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Manager</span>
                                <input type="text" name="manager_name" value="{{ old('manager_name', $outletModel?->manager_name) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Gudang Terkait</span>
                                <select name="warehouse_id" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    <option value="">Belum dihubungkan</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $outletModel?->warehouse_id) === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Buka</span>
                                <input type="date" name="opening_date" value="{{ old('opening_date', $outletModel?->opening_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Alamat</span>
                                <textarea name="address" rows="4" class="mt-2 w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">{{ old('address', $outletModel?->address) }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                            KPI Operasional
                        </span>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Status Outlet</span>
                                <select name="status" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $outletModel?->status ?? 'active') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Daily Sales Target</span>
                                <input type="number" step="0.01" min="0" name="daily_sales_target" value="{{ old('daily_sales_target', $outletModel?->daily_sales_target ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Service Level (%)</span>
                                <input type="number" step="0.01" min="0" max="100" name="service_level" value="{{ old('service_level', $outletModel?->service_level ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Inventory Accuracy (%)</span>
                                <input type="number" step="0.01" min="0" max="100" name="inventory_accuracy" value="{{ old('inventory_accuracy', $outletModel?->inventory_accuracy ?? 0) }}" class="mt-2 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
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
                            <label class="flex items-start gap-3 rounded-[24px] border border-gray-200 bg-gray-50/80 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <input type="hidden" name="is_fulfillment_hub" value="0">
                                <input type="checkbox" name="is_fulfillment_hub" value="1" @checked(old('is_fulfillment_hub', $outletModel?->is_fulfillment_hub)) class="mt-1 h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">Fulfillment Hub</span>
                                    <span class="mt-1 block text-sm leading-6 text-gray-500 dark:text-gray-400">Aktifkan jika outlet juga menangani pickup, ship-from-store, atau peran distribusi internal.</span>
                                </span>
                            </label>

                            <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Checklist multi outlet</p>
                                <ul class="mt-3 space-y-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    <li>Kode outlet wajib unik agar transaksi, stok, dan penugasan tim tidak tercampur antar cabang.</li>
                                    <li>Hubungkan outlet ke gudang bila replenishment, transfer, dan stock opname ingin dibaca secara presisi.</li>
                                    <li>Service level dan inventory accuracy menjadi KPI dasar untuk control tower retail.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Finalisasi</p>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Simpan perubahan untuk memperbarui struktur cabang yang dipakai penjualan, stok, HR, dan dashboard KPI outlet.</p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                Simpan Outlet
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
