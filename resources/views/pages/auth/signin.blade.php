@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 min-h-screen bg-white p-4 sm:p-6 dark:bg-gray-900">
        <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col overflow-y-auto lg:flex-row">
            <div class="flex w-full flex-1 items-center py-8 lg:w-1/2 lg:py-12">
                <div class="mx-auto w-full max-w-md">
                    <a href="{{ route('login') }}"
                        class="mb-8 inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="mr-1 stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 20 20" fill="none">
                            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Login ERP
                    </a>

                    <h1 class="mb-2 text-title-sm font-semibold text-gray-800 sm:text-title-md dark:text-white/90">
                        Sign In
                    </h1>
                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                        Masuk menggunakan akun admin ERP Anda.
                    </p>

                    @if (session('success'))
                        <div
                            class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Email
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-white/30" />
                        </div>

                        <div>
                            <label for="password"
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Password
                            </label>
                            <div x-data="{ showPassword: false }" class="relative">
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                    autocomplete="current-password" placeholder="Masukkan password Anda"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-white/30" />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                    <span x-text="showPassword ? 'Hide' : 'Show'" class="text-xs font-medium"></span>
                                </button>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center text-sm text-gray-700 dark:text-gray-400">
                            <input type="checkbox" name="remember" value="1" class="mr-2 rounded border-gray-300"
                                {{ old('remember') ? 'checked' : '' }}>
                            Tetap login
                        </label>

                        <div class="flex justify-end">
                            <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:underline dark:text-brand-400">
                                Lupa password?
                            </a>
                        </div>

                        <button type="submit"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            Sign In
                        </button>
                    </form>

                    <p class="mt-5 text-sm text-gray-700 dark:text-gray-400">
                        Belum punya akun?
                        <a href="{{ route('register') }}"
                            class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Register</a>
                    </p>
                </div>
            </div>

            <div class="bg-brand-950 relative hidden w-full items-center justify-center rounded-2xl p-10 lg:flex lg:w-1/2 dark:bg-white/5">
                <x-common.common-grid-shape />
                <div class="z-1 flex max-w-xs flex-col items-center text-center">
                    <a href="{{ route('login') }}" class="mb-4 block">
                        <img src="{{ asset('images/logo/auth-logo.svg') }}" alt="Logo" />
                    </a>
                    <p class="text-gray-400 dark:text-white/60">
                        WebStellar ERP - akses terpusat untuk operasional harian.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
