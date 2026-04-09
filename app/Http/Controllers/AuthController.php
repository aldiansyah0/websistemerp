<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.auth.signin');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password tidak valid.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('pages.auth.signup', [
            'registrationLocked' => User::query()->exists(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Registrasi dinonaktifkan karena akun admin sudah tersedia.',
                ]);
        }

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tenantId = (int) Tenant::query()->firstOrCreate(
            ['code' => 'default'],
            ['name' => 'Default Tenant', 'is_active' => true]
        )->id;

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $payload['password'],
            'tenant_id' => $tenantId,
            'location_id' => null,
            'active_location_id' => null,
            'access_scope' => User::ACCESS_SCOPE_ALL,
            'email_verified_at' => now(),
        ]);

        $roleId = Role::query()
            ->where('slug', Role::OWNER)
            ->value('id');

        if ($roleId !== null) {
            $user->roles()->syncWithoutDetaching([(int) $roleId]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Registrasi berhasil. Selamat datang di WebStellar ERP.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}
