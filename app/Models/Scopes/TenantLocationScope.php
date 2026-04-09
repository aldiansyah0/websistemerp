<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TenantLocationScope implements Scope
{
    /**
     * @var array<string, bool>
     */
    private static array $columnCache = [];

    /**
     * @var array<string, array{constrain: bool, location_ids: array<int, int>}>
     */
    private static array $userLocationPayloadCache = [];

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $table = $model->getTable();

        if ($this->shouldScopeTenant($model) && filled($user->tenant_id) && $this->hasColumn($table, 'tenant_id')) {
            $builder->where($table . '.tenant_id', (int) $user->tenant_id);
        }

        if ($this->shouldScopeLocation($model) && $this->hasColumn($table, 'location_id')) {
            $locationScopePayload = $this->resolveLocationScopePayload($user);

            if (! $locationScopePayload['constrain']) {
                return;
            }

            if ($locationScopePayload['location_ids'] === []) {
                $builder->whereRaw('1 = 0');

                return;
            }

            if (count($locationScopePayload['location_ids']) === 1) {
                $builder->where(
                    $table . '.location_id',
                    $locationScopePayload['location_ids'][0]
                );

                return;
            }

            $builder->whereIn($table . '.location_id', $locationScopePayload['location_ids']);
        }
    }

    private function shouldScopeTenant(Model $model): bool
    {
        if (method_exists($model, 'usesTenantScope')) {
            return (bool) $model->usesTenantScope();
        }

        return false;
    }

    private function shouldScopeLocation(Model $model): bool
    {
        if (method_exists($model, 'usesLocationScope')) {
            return (bool) $model->usesLocationScope();
        }

        return false;
    }

    private function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;

        if (! array_key_exists($cacheKey, self::$columnCache)) {
            self::$columnCache[$cacheKey] = Schema::hasColumn($table, $column);
        }

        return self::$columnCache[$cacheKey];
    }

    /**
     * @return array{constrain: bool, location_ids: array<int, int>}
     */
    private function resolveLocationScopePayload(User $user): array
    {
        $hasGlobalAccess = $user->hasGlobalLocationAccess();

        $cacheKey = implode(':', [
            (string) $user->id,
            (string) $user->effectiveAccessScope(),
            (string) ($user->tenant_id ?? 'null'),
            (string) ($user->location_id ?? 'null'),
            (string) ($user->active_location_id ?? 'null'),
            $hasGlobalAccess ? 'global' : 'restricted',
        ]);

        if (array_key_exists($cacheKey, self::$userLocationPayloadCache)) {
            return self::$userLocationPayloadCache[$cacheKey];
        }

        if ($hasGlobalAccess || ! $user->shouldConstrainLocation()) {
            return self::$userLocationPayloadCache[$cacheKey] = [
                'constrain' => false,
                'location_ids' => [],
            ];
        }

        return self::$userLocationPayloadCache[$cacheKey] = [
            'constrain' => true,
            'location_ids' => $user->scopedLocationIds(),
        ];
    }
}
