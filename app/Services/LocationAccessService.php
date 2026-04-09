<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LocationAccessService
{
    /**
     * @return Collection<int, Location>
     */
    public function availableLocations(User $user): Collection
    {
        if ($user->hasGlobalLocationAccess()) {
            return Location::query()
                ->orderBy('type')
                ->orderBy('name')
                ->get();
        }

        $scope = $user->effectiveAccessScope();

        if ($scope === User::ACCESS_SCOPE_ASSIGNED) {
            $locations = $user->allowedLocations()
                ->orderBy('type')
                ->orderBy('name')
                ->get();

            if ($locations->isNotEmpty()) {
                return $locations;
            }

            if ($user->location_id !== null) {
                $fallback = Location::query()->whereKey((int) $user->location_id)->first();
                if ($fallback !== null) {
                    return collect([$fallback]);
                }
            }

            return collect();
        }

        $singleLocationId = $user->resolveActiveLocationId();

        if ($singleLocationId === null) {
            return collect();
        }

        $singleLocation = Location::query()->whereKey($singleLocationId)->first();

        return $singleLocation ? collect([$singleLocation]) : collect();
    }

    public function switchActiveLocation(User $user, ?int $locationId): User
    {
        $scope = $user->effectiveAccessScope();

        if ($locationId === null) {
            if ($scope === User::ACCESS_SCOPE_SINGLE && ! $user->hasGlobalLocationAccess()) {
                throw new DomainException('User single-location wajib memilih satu lokasi aktif.');
            }

            $user->forceFill([
                'active_location_id' => null,
                'location_id' => null,
            ])->save();

            return $user->refresh();
        }

        $locationExists = Location::query()->whereKey($locationId)->exists();
        if (! $locationExists) {
            throw new DomainException('Lokasi yang dipilih tidak ditemukan.');
        }

        if (! $user->canAccessLocation($locationId)) {
            throw new DomainException('Anda tidak memiliki akses ke outlet/gudang yang dipilih.');
        }

        $user->forceFill([
            'active_location_id' => $locationId,
            'location_id' => $locationId,
        ])->save();

        return $user->refresh();
    }

    /**
     * @param array<int, int> $locationIds
     */
    public function syncAssignedLocations(User $user, array $locationIds, ?int $activeLocationId = null): User
    {
        return DB::transaction(function () use ($user, $locationIds, $activeLocationId): User {
            $normalizedLocationIds = collect($locationIds)
                ->filter(fn ($id): bool => filled($id))
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($normalizedLocationIds !== []) {
                $validIds = Location::query()->whereIn('id', $normalizedLocationIds)->pluck('id')->map(fn ($id): int => (int) $id)->all();
                if (count($validIds) !== count($normalizedLocationIds)) {
                    throw new DomainException('Terdapat lokasi yang tidak valid pada assignment user.');
                }
            }

            $user->allowedLocations()->sync($normalizedLocationIds);

            if ($user->effectiveAccessScope() === User::ACCESS_SCOPE_SINGLE && count($normalizedLocationIds) === 1) {
                $activeLocationId = $normalizedLocationIds[0];
            }

            if ($activeLocationId !== null) {
                if (! in_array($activeLocationId, $normalizedLocationIds, true) && ! $user->hasGlobalLocationAccess()) {
                    throw new DomainException('Lokasi aktif harus termasuk dalam daftar lokasi yang diizinkan.');
                }
            }

            $user->forceFill([
                'active_location_id' => $activeLocationId,
                'location_id' => $activeLocationId,
            ])->save();

            return $user->refresh();
        });
    }
}
