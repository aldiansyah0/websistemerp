@extends('layouts.fullscreen-layout')
@section('content')
<div class="flex min-h-screen items-center justify-center p-6 bg-white dark:bg-gray-900">
    <div class="text-center">
        <p class="text-6xl font-bold text-gray-200 dark:text-gray-700">500</p>
        <h1 class="mt-4 text-2xl font-semibold text-gray-800 dark:text-white">Terjadi Kesalahan Server</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Sistem mengalami gangguan sementara. Tim teknis sedang menangani masalah ini.
        </p>
        <div class="mt-8">
            <a href="{{ route('dashboard') }}"
                class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
