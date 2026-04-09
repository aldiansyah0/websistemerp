@extends('layouts.fullscreen-layout')

@section('content')
<div class="relative z-1 flex min-h-screen items-center justify-center bg-white p-6 dark:bg-gray-900">
    <div class="w-full max-w-md">
        <div class="mb-8">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                &larr; Kembali ke Login
            </a>
        </div>

        <h1 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white">Lupa Password</h1>
        <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">
            Masukkan email yang terdaftar. Kami akan mengirimkan link untuk reset password.
        </p>

        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Email
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    placeholder="nama@perusahaan.com">
            </div>

            <button type="submit"
                class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600">
                Kirim Link Reset Password
            </button>
        </form>
    </div>
</div>
@endsection
