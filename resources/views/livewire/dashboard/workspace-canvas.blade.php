@php
    use App\Helpers\MenuHelper;

    $menuGroups = MenuHelper::getMenuGroups();
@endphp

<div class="space-y-6">
    <section
        class="relative overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(14,165,233,0.10),_transparent_32%),linear-gradient(135deg,rgba(15,23,42,0.02),transparent_55%)] dark:bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.12),_transparent_30%),linear-gradient(135deg,rgba(148,163,184,0.08),transparent_60%)]">
        </div>
        <div class="relative px-6 py-8 md:px-8 md:py-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <span
                        class="inline-flex rounded-full border border-gray-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                        {{ $pageEyebrow }}
                    </span>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">
                        {{ $pageTitle }}
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">
                        {{ $pageDescription }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div
                        class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Status</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Siap dikembangkan</p>
                    </div>
                    <div
                        class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Sidebar</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Dropdown aktif</p>
                    </div>
                    <div
                        class="col-span-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-1">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Template</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Blank workspace</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_380px]">
        <div
            class="rounded-[28px] border border-dashed border-gray-300 bg-white px-6 py-8 shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="flex min-h-[420px] flex-col items-center justify-center text-center">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-[28px] bg-gray-900 text-white shadow-lg shadow-gray-900/10 dark:bg-white dark:text-gray-900">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 18.5H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <path d="M7.5 15.5V10.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <path d="M12 15.5V6.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <path d="M16.5 15.5V12.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                    </svg>
                </div>
                <h2 class="mt-6 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Area konten masih kosong
                </h2>
                <p class="mt-3 max-w-lg text-sm leading-6 text-gray-500 dark:text-gray-400">
                    Layout dashboard sudah dibersihkan dari komponen demo. Anda sekarang punya kanvas yang rapi
                    untuk menambahkan tabel, kartu statistik, form input, atau modul operasional ERP berikutnya.
                </p>
                <div
                    class="mt-6 inline-flex rounded-full border border-dashed border-gray-300 px-4 py-2 text-xs font-medium uppercase tracking-[0.26em] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Blank Workspace Ready
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @foreach ($menuGroups as $menuGroup)
                <div
                    class="rounded-[28px] border border-gray-200 bg-white px-5 py-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $menuGroup['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Struktur menu untuk area ini sudah siap dan dapat dikembangkan per modul.
                    </p>

                    <div class="mt-5 space-y-3">
                        @foreach ($menuGroup['items'] as $item)
                            @php
                                $hasSubItems = !empty($item['subItems']);
                                $isItemActive = !$hasSubItems && isset($item['path']) ? MenuHelper::isActive($item['path']) : false;
                                $isSubmenuActive = $hasSubItems ? MenuHelper::hasActiveSubItems($item['subItems']) : false;
                            @endphp

                            <div
                                class="rounded-2xl border px-4 py-4 {{ $isItemActive || $isSubmenuActive ? 'border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900' : 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300' }}">
                                <div class="flex items-center gap-3">
                                    <span class="shrink-0">
                                        {!! MenuHelper::getIconSvg($item['icon']) !!}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold">{{ $item['name'] }}</p>
                                        @if ($hasSubItems)
                                            <p class="mt-1 text-xs opacity-80">{{ count($item['subItems']) }} submenu siap dipakai
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if ($hasSubItems)
                                    <div class="mt-4 space-y-2 pl-1">
                                        @foreach ($item['subItems'] as $subItem)
                                            @php
                                                $isSubItemActive = MenuHelper::isActive($subItem['path']);
                                            @endphp
                                            <div
                                                class="flex items-center rounded-xl px-3 py-2 text-sm {{ $isSubItemActive ? 'bg-white/15 font-semibold dark:bg-gray-900/10' : 'bg-white/70 text-gray-600 dark:bg-white/[0.04] dark:text-gray-300' }}">
                                                <span class="mr-2 text-xs">&bull;</span>
                                                <span>{{ $subItem['name'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
