<?php

namespace App\Models\Scopes;

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

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $table = $model->getTable();

        if ($this->shouldScopeTenant($model) && filled($user->tenant_id) && $this->hasColumn($table, 'tenant_id')) {
            $builder->where($table . '.tenant_id', (int) $user->tenant_id);
        }

        if ($this->shouldScopeLocation($model) && filled($user->location_id) && $this->hasColumn($table, 'location_id')) {
            $builder->where($table . '.location_id', (int) $user->location_id);
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
}
