@php
    use App\Helpers\MenuHelper;

    $menuGroups = MenuHelper::getMenuGroups();
    $initialOpenSubmenus = [];

    foreach ($menuGroups as $groupIndex => $menuGroup) {
        foreach ($menuGroup['items'] as $itemIndex => $item) {
            if (!empty($item['subItems']) && MenuHelper::hasActiveSubItems($item['subItems'])) {
                $initialOpenSubmenus["{$groupIndex}-{$itemIndex}"] = true;
            }
        }
    }
@endphp

<aside id="sidebar"
    class="fixed left-0 top-0 z-99999 mt-0 flex h-screen flex-col border-r border-gray-200 bg-white/95 px-5 text-gray-900 shadow-sm backdrop-blur-sm transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900/95"
    x-data="{
        openSubmenus: @js($initialOpenSubmenus),
        toggleSubmenu(key) {
            this.openSubmenus[key] = !this.openSubmenus[key];
        },
        isSubmenuOpen(key) {
            return !!this.openSubmenus[key];
        }
    }"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">
    <div class="flex pt-8 pb-7"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
        'xl:justify-center' :
        'justify-start'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gray-900 text-sm font-semibold tracking-[0.32em] text-white dark:bg-white dark:text-gray-900">
                WE
            </span>
            <span x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                <span class="block text-sm font-semibold text-gray-900 dark:text-white">WebStellar ERP</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">Core Workspace</span>
            </span>
        </a>
    </div>

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mb-6">
            <div class="flex flex-col gap-6">
                @foreach ($menuGroups as $groupIndex => $menuGroup)
                    <div>
                        <h2 class="mb-4 flex text-xs leading-[20px] uppercase tracking-[0.28em] text-gray-400"
                            :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                            'lg:justify-center' : 'justify-start'">
                            <template
                                x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                                <span>{{ $menuGroup['title'] }}</span>
                            </template>
                            <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                        fill="currentColor" />
                                </svg>
                            </template>
                        </h2>

                        <ul class="flex flex-col gap-1.5">
                            @foreach ($menuGroup['items'] as $itemIndex => $item)
                                @php
                                    $submenuKey = "{$groupIndex}-{$itemIndex}";
                                    $hasSubItems = !empty($item['subItems']);
                                    $isActive = !$hasSubItems && isset($item['path']) ? MenuHelper::isActive($item['path']) : false;
                                    $isSubmenuActive = $hasSubItems ? MenuHelper::hasActiveSubItems($item['subItems']) : false;
                                @endphp

                                <li>
                                    @if ($hasSubItems)
                                        <button type="button"
                                            @click="toggleSubmenu('{{ $submenuKey }}')"
                                            class="menu-item group w-full"
                                            :class="[
                                                isSubmenuOpen('{{ $submenuKey }}') || {{ $isSubmenuActive ? 'true' : 'false' }} ?
                                                'menu-item-active' : 'menu-item-inactive',
                                                (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                                'xl:justify-center' :
                                                'justify-start'
                                            ]">
                                            <span
                                                :class="isSubmenuOpen('{{ $submenuKey }}') || {{ $isSubmenuActive ? 'true' : 'false' }} ?
                                                    'menu-item-icon-active' :
                                                    'menu-item-icon-inactive'">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <span
                                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="menu-item-text">
                                                {{ $item['name'] }}
                                            </span>

                                            <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="ml-auto h-5 w-5 transition-transform duration-200"
                                                :class="{ 'rotate-180 text-brand-500': isSubmenuOpen('{{ $submenuKey }}') }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <div x-show="isSubmenuOpen('{{ $submenuKey }}') && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)"
                                            x-transition>
                                            <ul class="ml-9 mt-2 space-y-1">
                                                @foreach ($item['subItems'] as $subItem)
                                                    @php
                                                        $isSubItemActive = MenuHelper::isActive($subItem['path']);
                                                    @endphp
                                                    <li>
                                                        <a href="{{ $subItem['path'] }}" class="menu-dropdown-item"
                                                            :class="'{{ $isSubItemActive ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}'">
                                                            {{ $subItem['name'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <a href="{{ $item['path'] }}"
                                            class="menu-item group {{ $isActive ? 'menu-item-active' : 'menu-item-inactive' }}"
                                            :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                                'xl:justify-center' :
                                                'justify-start'">
                                            <span
                                                class="{{ $isActive ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <span
                                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="menu-item-text">
                                                {{ $item['name'] }}
                                            </span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

        <div x-data x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
            x-transition class="mt-auto">
            @include('layouts.sidebar-widget')
        </div>
    </div>
</aside>

<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50"></div>
