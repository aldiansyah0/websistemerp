@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
            {{ isset($user) ? 'Edit User' : 'Tambah User' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ isset($user) ? 'Perbarui data akun, role, dan hak akses lokasi.' : 'Buat akun user baru dengan role dan hak akses lokasi.' }}
        </p>
    </div>

    <form method="POST"
        action="{{ isset($user) ? route('user-management.update', $user) : route('user-management.store') }}"
        class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        {{-- Nama --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}
            </label>
            <input type="password" name="password" autocomplete="new-password"
                {{ isset($user) ? '' : 'required' }}
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white"
                placeholder="Minimal 8 karakter">
            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:text-white">
        </div>

        {{-- Role --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
            <select name="role" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}"
                        {{ old('role', $user->roles->first()?->slug ?? '') === $role->slug ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Scope Akses --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Scope Akses</label>
            <select name="access_scope" required
                class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="single_location" {{ old('access_scope', $user->access_scope ?? 'single_location') === 'single_location' ? 'selected' : '' }}>Satu Lokasi</option>
                <option value="assigned_locations" {{ old('access_scope', $user->access_scope ?? '') === 'assigned_locations' ? 'selected' : '' }}>Lokasi yang Ditentukan</option>
                <option value="all_locations" {{ old('access_scope', $user->access_scope ?? '') === 'all_locations' ? 'selected' : '' }}>Semua Lokasi</option>
            </select>
            @error('access_scope')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Lokasi Utama --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lokasi Utama (opsional)</label>
            <select name="location_id"
                class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">-- Tidak Ada --</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}"
                        {{ old('location_id', $user->location_id ?? '') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }} ({{ $location->type }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tombol --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                {{ isset($user) ? 'Simpan Perubahan' : 'Buat User' }}
            </button>
            <a href="{{ route('user-management') }}"
                class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
