<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | WebStellar ERP</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-950');
                        body.classList.remove('bg-gray-50');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-950');
                        body.classList.add('bg-gray-50');
                    }
                }
            });

            Alpine.store('notifications', {
                toasts: [],

                add(notification) {
                    const id = Date.now();
                    notification.id = id;
                    notification.type = notification.type || 'info';

                    this.toasts.push(notification);

                    // Auto-remove after 5 seconds for success, 7 seconds for error
                    const duration = notification.type === 'error' ? 7000 : 5000;
                    setTimeout(() => {
                        this.remove(id);
                    }, duration);

                    return id;
                },

                remove(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            const applyBodyTheme = () => {
                if (!document.body) {
                    return;
                }

                if (theme === 'dark') {
                    document.body.classList.add('dark', 'bg-gray-950');
                    document.body.classList.remove('bg-gray-50');
                } else {
                    document.body.classList.remove('dark', 'bg-gray-950');
                    document.body.classList.add('bg-gray-50');
                }
            };

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            if (document.body) {
                applyBodyTheme();
            } else {
                document.addEventListener('DOMContentLoaded', applyBodyTheme, {
                    once: true
                });
            }
        })();
    </script>
    
</head>

<body class="bg-gray-50 dark:bg-gray-950"
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2" role="region" aria-live="polite" aria-atomic="true">
        <template x-for="toast in $store.notifications.toasts" :key="toast.id">
            <div class="animate-slideIn rounded-lg shadow-lg p-4 min-w-[300px]"
                :class="{
                    'bg-success-50 border border-success-200 text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-300': toast.type === 'success',
                    'bg-error-50 border border-error-200 text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-300': toast.type === 'error',
                    'bg-info-50 border border-info-200 text-info-700 dark:bg-info-500/10 dark:border-info-500/20 dark:text-info-300': toast.type === 'info',
                }">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <template x-if="toast.type === 'success'">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12zM9 9a1 1 0 100-2 1 1 0 000 2zm-.25 5a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm2.25-2.5a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" /></svg>
                        </template>
                        <div>
                            <p class="text-sm font-semibold" x-text="toast.message"></p>
                        </div>
                    </div>
                    <button @click="$store.notifications.remove(toast.id)" class="text-current opacity-60 hover:opacity-100">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @if (session('success') || session('error') || $errors->any())
                    <div class="mb-6 space-y-3">
                        @if (session('success'))
                            <div class="rounded-2xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm font-medium text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-200">
                                <p class="font-semibold">Masih ada data yang perlu diperbaiki.</p>
                                <ul class="mt-2 list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

    </div>

</body>

@livewireScripts
@stack('scripts')

<script>
    // Listen for Livewire notify events
    document.addEventListener('livewire:navigated', () => {
        Livewire.on('notify', (message, type = 'info') => {
            Alpine.store('notifications').add({
                message: message,
                type: type
            });
        });
    });

    // Also set up the listener immediately
    if (window.Livewire) {
        Livewire.on('notify', (message, type = 'info') => {
            Alpine.store('notifications').add({
                message: message,
                type: type
            });
        });
    }
</script>

</html>
