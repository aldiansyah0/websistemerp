<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Scopes\TenantLocationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenantLocation
{
    private static ?int $resolvedDefaultTenantId = null;

    protected static function bootBelongsToTenantLocation(): void
    {
        static::addGlobalScope(new TenantLocationScope());

        static::creating(function (Model $model): void {
            $user = Auth::user();

            $tenantId = $user?->tenant_id ?? static::defaultTenantId();
            if (method_exists($model, 'usesTenantScope') && $model->usesTenantScope() && blank($model->getAttribute('tenant_id')) && filled($tenantId)) {
                $model->setAttribute('tenant_id', (int) $tenantId);
            }

            $resolvedLocationId = self::resolvedUserLocationId($user);

            if (method_exists($model, 'usesLocationScope') && $model->usesLocationScope() && blank($model->getAttribute('location_id')) && filled($resolvedLocationId)) {
                $model->setAttribute('location_id', (int) $resolvedLocationId);
            }
        });
    }

    public function usesTenantScope(): bool
    {
        return property_exists($this, 'tenantScoped') ? (bool) $this->tenantScoped : true;
    }

    public function usesLocationScope(): bool
    {
        return property_exists($this, 'locationScoped') ? (bool) $this->locationScoped : false;
    }

    public function scopeWithoutTenantLocation(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantLocationScope::class);
    }

    private static function defaultTenantId(): ?int
    {
        if (static::$resolvedDefaultTenantId !== null) {
            return static::$resolvedDefaultTenantId > 0 ? static::$resolvedDefaultTenantId : null;
        }

        if (! Schema::hasTable('tenants')) {
            static::$resolvedDefaultTenantId = 0;

            return null;
        }

        static::$resolvedDefaultTenantId = (int) Tenant::query()->where('code', 'default')->value('id');

        return static::$resolvedDefaultTenantId > 0 ? static::$resolvedDefaultTenantId : null;
    }

    private static function resolvedUserLocationId(mixed $user): ?int
    {
        if (! $user instanceof User) {
            return null;
        }

        return $user->resolveActiveLocationId();
    }
}
