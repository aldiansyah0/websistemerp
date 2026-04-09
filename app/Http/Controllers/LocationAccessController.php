<?php

namespace App\Http\Controllers;

use App\Http\Requests\SwitchActiveLocationRequest;
use App\Models\User;
use App\Services\LocationAccessService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class LocationAccessController extends Controller
{
    public function switchActive(SwitchActiveLocationRequest $request, LocationAccessService $locationAccessService): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403, 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        try {
            $locationAccessService->switchActiveLocation(
                $user,
                $request->validated('location_id')
            );
        } catch (DomainException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', 'Konteks outlet/gudang aktif berhasil diperbarui.');
    }
}
