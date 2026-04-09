<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['roles', 'activeLocation'])
            ->withoutGlobalScopes()
            ->orderBy('name')
            ->paginate(25);

        return view('pages.operations.user-management', compact('users'));
    }

    public function create(): View
    {
        $roles     = Role::query()->orderBy('name')->get();
        $locations = Location::query()->where('status', 'active')->orderBy('name')->get();

        return view('pages.operations.user-form', compact('roles', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'unique:users,email'],
            'password'            => ['required', 'string', 'min:8', 'confirmed'],
            'role'                => ['required', 'string', 'exists:roles,slug'],
            'access_scope'        => ['required', 'in:all_locations,assigned_locations,single_location'],
            'location_id'         => ['nullable', 'exists:locations,id'],
            'allowed_locations'   => ['nullable', 'array'],
            'allowed_locations.*' => ['exists:locations,id'],
        ], $this->messages());

        /** @var \App\Models\User $authUser */
        $authUser = $request->user();

        $user = User::query()->create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'tenant_id'          => $authUser->tenant_id,
            'access_scope'       => $validated['access_scope'],
            'location_id'        => $validated['location_id'] ?? null,
            'active_location_id' => $validated['location_id'] ?? null,
            'email_verified_at'  => now(),
        ]);

        $user->assignRole($validated['role']);

        if (! empty($validated['allowed_locations'])) {
            $user->allowedLocations()->sync($validated['allowed_locations']);
        }

        return redirect()->route('user-management')
            ->with('success', 'User ' . $user->name . ' berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $roles     = Role::query()->orderBy('name')->get();
        $locations = Location::query()->where('status', 'active')->orderBy('name')->get();
        $user->load(['roles', 'allowedLocations']);

        return view('pages.operations.user-form', compact('user', 'roles', 'locations'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'            => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'                => ['required', 'string', 'exists:roles,slug'],
            'access_scope'        => ['required', 'in:all_locations,assigned_locations,single_location'],
            'location_id'         => ['nullable', 'exists:locations,id'],
            'allowed_locations'   => ['nullable', 'array'],
            'allowed_locations.*' => ['exists:locations,id'],
        ], $this->messages());

        $data = [
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'access_scope'       => $validated['access_scope'],
            'location_id'        => $validated['location_id'] ?? null,
            'active_location_id' => $validated['location_id'] ?? null,
        ];

        if (filled($validated['password'] ?? null)) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        $roleId = Role::query()->where('slug', $validated['role'])->value('id');
        if ($roleId) {
            $user->roles()->sync([$roleId]);
        }

        $user->allowedLocations()->sync($validated['allowed_locations'] ?? []);

        return redirect()->route('user-management')
            ->with('success', 'User ' . $user->name . ' berhasil diperbarui.');
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required'      => 'Role wajib dipilih.',
            'access_scope.required' => 'Scope akses wajib dipilih.',
        ];
    }
}
