@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();
    $initials = collect(explode(' ', trim((string) ($authUser?->name ?? 'ERP User'))))
        ->filter()
        ->map(fn (string $chunk): string => strtoupper(substr($chunk, 0, 1)))
        ->take(2)
        ->implode('');
    $roleNames = $authUser?->roles()->pluck('name')->implode(', ');
    $activeLocation = $authUser?->activeLocation?->name ?? $authUser?->location?->name;
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <button class="header-action flex items-center text-gray-700 dark:text-gray-400" @click.prevent="toggleDropdown()" type="button" aria-label="Open user menu" x-bind:aria-expanded="dropdownOpen" title="Account">
        <span
            class="mr-3 header-avatar flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-gray-900 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">
            {{ $initials !== '' ? $initials : 'WS' }}
        </span>

        <span class="sr-only header-username">{{ $authUser?->name ?? 'ERP User' }}</span>

        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }" fill="none"
            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-[17px] flex w-[260px] max-w-[calc(100vw-2rem)] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
        style="display: none;">
        <div>
            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                {{ $roleNames !== '' ? $roleNames : 'Role belum ditetapkan' }}
            </span>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ $authUser?->email ?? 'guest@webstellar.local' }}</span>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
                {{ $activeLocation ? 'Lokasi aktif: ' . $activeLocation : 'Lokasi aktif: semua lokasi yang diizinkan' }}
            </span>
        </div>

        <ul class="flex flex-col gap-1 border-b border-gray-200 pb-3 pt-4 dark:border-gray-800">
            <li>
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    <span class="text-gray-500 dark:text-gray-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H10.25C11.3546 4.75 12.25 5.64543 12.25 6.75V10.25C12.25 11.3546 11.3546 12.25 10.25 12.25H6.75C5.64543 12.25 4.75 11.3546 4.75 10.25V6.75Z" stroke="currentColor" stroke-width="1.5" />
                            <path d="M15.25 5.75H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M15.25 9.25H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M15.25 14.75H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('warehouse') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    <span class="text-gray-500 dark:text-gray-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3.75 9.25L12 4.75L20.25 9.25V18.25C20.25 18.8023 19.8023 19.25 19.25 19.25H4.75C4.19772 19.25 3.75 18.8023 3.75 18.25V9.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            <path d="M8.25 19.25V12.75H15.75V19.25" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        </svg>
                    </span>
                    Warehouse
                </a>
            </li>
            <li>
                <a href="{{ route('supplier') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                    <span class="text-gray-500 dark:text-gray-400">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7.75 8.25C7.75 6.59315 9.09315 5.25 10.75 5.25H13.25C14.9069 5.25 16.25 6.59315 16.25 8.25C16.25 9.90685 14.9069 11.25 13.25 11.25H10.75C9.09315 11.25 7.75 9.90685 7.75 8.25Z" stroke="currentColor" stroke-width="1.5" />
                            <path d="M4.75 18.75C4.75 15.9886 6.98858 13.75 9.75 13.75H14.25C17.0114 13.75 19.25 15.9886 19.25 18.75V19.25H4.75V18.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        </svg>
                    </span>
                    Supplier
                </a>
            </li>
        </ul>

        <a href="{{ route('dashboard') }}"
            class="mt-3 flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            @click="closeDropdown()">
            <span class="text-gray-500 dark:text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 12h16m-7-7 7 7-7 7"></path>
                </svg>
            </span>
            Kembali ke dashboard
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-red-600 text-theme-sm hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-900/20"
                @click="closeDropdown()">
                <span class="text-red-500 dark:text-red-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"></path>
                    </svg>
                </span>
                Logout
            </button>
        </form>
    </div>
</div>
