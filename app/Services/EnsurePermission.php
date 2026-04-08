<?php

namespace App\Services;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * @param array<int, string> $permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            if (app()->environment(['local', 'testing'])) {
                return $next($request);
            }

            abort(403, 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        if ($user->hasRole(Role::OWNER)) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak. Role Anda tidak memiliki izin untuk proses ini.');
    }
}
