@extends('layouts.fullscreen-layout')
@section('content')
<div class="flex min-h-screen items-center justify-center p-6 bg-white dark:bg-gray-900">
    <div class="text-center">
        <p class="text-6xl font-bold text-gray-200 dark:text-gray-700">403</p>
        <h1 class="mt-4 text-2xl font-semibold text-gray-800 dark:text-white">Akses Ditolak</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Anda tidak memiliki izin untuk mengakses halaman ini.<br>
            Hubungi administrator jika merasa ini adalah kesalahan.
        </p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ url()->previous() }}"
                class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                &larr; Kembali
            </a>
            <a href="{{ route('dashboard') }}"
                class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
