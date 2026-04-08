@php
    $trendClasses = [
        'up' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
        'down' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400',
        'neutral' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',
    ];

    $severityClasses = [
        'critical' => 'border-error-200 bg-error-50/70 dark:border-error-500/20 dark:bg-error-500/10',
        'high' => 'border-warning-200 bg-warning-50/70 dark:border-warning-500/20 dark:bg-warning-500/10',
        'medium' => 'border-blue-light-200 bg-blue-light-50/70 dark:border-blue-light-500/20 dark:bg-blue-light-500/10',
    ];

    $supplierStatusClasses = [
        'Preferred' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'Stable' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300',
        'Watch' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'Critical' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
        'Inactive' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];

    $outletStatusClasses = [
        'Hub' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'Active' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300',
        'Renovation' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'Inactive' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_32%),radial-gradient(circle_at_90%_20%,_rgba(249,115,22,0.14),_transparent_28%),linear-gradient(135deg,rgba(15,23,42,0.03),transparent_62%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_32%),radial-gradient(circle_at_90%_20%,_rgba(249,115,22,0.12),_transparent_28%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_62%)]"></div>
        <div class="relative px-6 py-7 md:px-8 md:py-8">
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.9fr)]">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                        <span>{{ $pageEyebrow }}</span>
                        <span class="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        <span>Snapshot {{ $generatedAt }}</span>
                    </div>
                    <h1 class="mt-5 max-w-2xl text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">{{ $pageTitle }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">
                        {{ $pageDescription }}
                        Fokus control tower saat ini adalah rantai data retail yang utuh: outlet, transaksi, stok, supplier, SDM, payroll, dan payment behavior.
                    </p>
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

    <section class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
        @foreach ($executiveKpis as $kpi)
            <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-400">{{ $kpi['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $kpi['value'] }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $trendClasses[$kpi['direction']] ?? $trendClasses['neutral'] }}">
                        {{ $kpi['trend'] }}
                    </span>
                </div>
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $kpi['support'] }}</p>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $kpi['footnote'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-error-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-error-700 dark:bg-error-500/10 dark:text-error-300">
                Replenishment Queue
            </span>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                SKU yang paling cepat memengaruhi lost sales dan keputusan pembelian
            </h2>
            <div class="mt-6 overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">SKU</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Produk</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Stok</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Cover</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                            @forelse ($replenishmentQueue as $item)
                                <tr>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $item['sku'] }}</td>
                                    <td class="px-5 py-4 align-top">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['category'] }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item['supplier'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item['stock'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item['days_cover'] }}</td>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item['action'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Belum ada SKU yang masuk antrean replenishment kritikal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                Alert
            </span>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Titik risiko yang perlu ditutup cepat
            </h2>
            <div class="mt-6 space-y-4">
                @foreach ($alerts as $alert)
                    <div class="rounded-[24px] border p-4 {{ $severityClasses[$alert['severity']] ?? $severityClasses['medium'] }}">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $alert['title'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $alert['detail'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-6 2xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                Category Mix
            </span>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Nilai stok, margin, dan cover perlu dibaca per kategori
            </h2>
            <div class="mt-6 space-y-4">
                @foreach ($categoryMix as $category)
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Margin {{ $category['margin'] }} / {{ $category['low_cover'] }} low cover</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category['stock_value'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $category['share'] }}% share nilai stok</p>
                            </div>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                            <div class="h-2 rounded-full bg-gradient-to-r from-blue-light-500 to-orange-400" style="width: {{ $category['share'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-success-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-success-700 dark:bg-success-500/10 dark:text-success-300">
                Procurement Queue
            </span>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Dokumen pembelian yang paling dekat ke suplai riil
            </h2>
            <div class="mt-6 overflow-hidden rounded-[28px] border border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">PO</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Supplier</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Lokasi</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">ETA</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Amount</th>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-[0.24em] text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                            @foreach ($procurementQueue as $po)
                                <tr>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $po['po_number'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['supplier'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['warehouse'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['eta'] }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $po['amount'] }}</td>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $po['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,1.2fr)]">
        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-blue-light-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                Outlet Pulse
            </span>
            <div class="mt-6 space-y-3">
                @foreach ($outletCards as $outlet)
                    <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $outlet['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $outlet['region'] }} / {{ $outlet['headcount'] }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $outletStatusClasses[$outlet['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ $outlet['status'] }}
                            </span>
                        </div>
                        <p class="mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $outlet['sales'] }}</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Service {{ $outlet['service_level'] }} / Split {{ $outlet['split_ratio'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                Workforce Readiness
            </span>
            <div class="mt-6 space-y-3">
                @foreach ($workforceCards as $card)
                    <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $card['title'] }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-warning-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                Payment Channel Mix
            </span>
            <div class="mt-6 grid gap-3 md:grid-cols-2">
                @foreach ($paymentChannelCards as $channel)
                    <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $channel['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $channel['provider'] }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $channel['share'] }}</p>
                        </div>
                        <p class="mt-3 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">{{ $channel['amount'] }}</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $channel['transactions'] }} transaksi / fee {{ $channel['fee'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.8fr)]">
        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                Warehouse Pulse
            </span>
            <div class="mt-6 space-y-3">
                @foreach ($warehouseCards as $warehouse)
                    <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $warehouse['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $warehouse['type'] }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $warehouse['stock_value'] }}</p>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $warehouse['active_skus'] }} / {{ $warehouse['critical'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                Supplier Snapshot
            </span>
            <div class="mt-6 space-y-3">
                @foreach ($supplierCards as $supplier)
                    <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $supplier['name'] }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Fill rate {{ $supplier['fill_rate'] }} / lead time {{ $supplier['lead_time'] }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $supplierStatusClasses[$supplier['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ $supplier['status'] }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Open PO value {{ $supplier['open_value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                KPI Logic
            </span>
            <div class="mt-6 space-y-4">
                @foreach ($formulaCards as $formula)
                    <div class="rounded-[24px] border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $formula['name'] }}</p>
                        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $formula['formula'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $formula['why'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            Live Modules
        </span>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($moduleMap as $module)
                <div class="rounded-[26px] border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $module['name'] }}</p>
                        <span class="inline-flex rounded-full bg-success-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ $module['status'] }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $module['detail'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
