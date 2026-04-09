@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Kelola User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manajemen akun, role, dan hak akses lokasi user sistem.</p>
        </div>
        <a href="{{ route('user-management.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Tambah User
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Role</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Scope Akses</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Lokasi Aktif</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @foreach($user->roles as $role)
                            <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                        {{ match($user->access_scope) {
                            'all_locations' => 'Semua Lokasi',
                            'assigned_locations' => 'Lokasi Ditentukan',
                            'single_location' => 'Satu Lokasi',
                            default => $user->access_scope,
                        } }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                        {{ $user->activeLocation?->name ?? 'Semua Lokasi' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('user-management.edit', $user) }}"
                            class="text-sm font-medium text-brand-500 hover:underline">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada user terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
