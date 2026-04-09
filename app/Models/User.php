<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ACCESS_SCOPE_ALL = 'all_locations';
    public const ACCESS_SCOPE_ASSIGNED = 'assigned_locations';
    public const ACCESS_SCOPE_SINGLE = 'single_location';

    /**
     * @var array<int, string>
     */
    private const ACCESS_SCOPES = [
        self::ACCESS_SCOPE_ALL,
        self::ACCESS_SCOPE_ASSIGNED,
        self::ACCESS_SCOPE_SINGLE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'location_id',
        'active_location_id',
        'access_scope',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'location_id' => 'integer',
            'active_location_id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function activeLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'active_location_id');
    }

    public function allowedLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'user_locations')->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->with('permissions')->withTimestamps();
    }

    public function hasRole(string|array $roleSlugs): bool
    {
        $roleSlugs = (array) $roleSlugs;

        if ($roleSlugs === []) {
            return false;
        }

        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('slug', $permissionSlug))
            ->exists();
    }

    public function assignRole(string $roleSlug): void
    {
        $roleId = Role::query()->where('slug', $roleSlug)->value('id');

        if ($roleId === null) {
            return;
        }

        $this->roles()->syncWithoutDetaching([$roleId]);
    }

    public function effectiveAccessScope(): string
    {
        $scope = (string) ($this->access_scope ?: self::ACCESS_SCOPE_SINGLE);

        if (! in_array($scope, self::ACCESS_SCOPES, true)) {
            return self::ACCESS_SCOPE_SINGLE;
        }

        return $scope;
    }

    public function hasGlobalLocationAccess(): bool
    {
        if ($this->hasRole([Role::OWNER, Role::SUPER_ADMIN])) {
            return true;
        }

        return $this->effectiveAccessScope() === self::ACCESS_SCOPE_ALL;
    }

    public function resolveActiveLocationId(): ?int
    {
        if (filled($this->location_id)) {
            return (int) $this->location_id;
        }

        if (filled($this->active_location_id)) {
            return (int) $this->active_location_id;
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    public function scopedLocationIds(): array
    {
        $scope = $this->effectiveAccessScope();

        if ($this->hasGlobalLocationAccess()) {
            return [];
        }

        $activeLocationId = $this->resolveActiveLocationId();

        if ($scope === self::ACCESS_SCOPE_SINGLE) {
            return $activeLocationId !== null ? [$activeLocationId] : [];
        }

        $allowedLocationIds = $this->allowedLocationIds();

        if ($activeLocationId !== null) {
            return [$activeLocationId];
        }

        return $allowedLocationIds;
    }

    public function shouldConstrainLocation(): bool
    {
        return ! $this->hasGlobalLocationAccess();
    }

    public function canAccessLocation(?int $locationId): bool
    {
        if ($locationId === null) {
            return $this->hasGlobalLocationAccess() || $this->effectiveAccessScope() === self::ACCESS_SCOPE_ASSIGNED;
        }

        if ($this->hasGlobalLocationAccess()) {
            return true;
        }

        if ($this->effectiveAccessScope() === self::ACCESS_SCOPE_ASSIGNED) {
            return in_array((int) $locationId, $this->allowedLocationIds(), true);
        }

        return $this->resolveActiveLocationId() === (int) $locationId;
    }

    /**
     * @return array<int, int>
     */
    public function allowedLocationIds(): array
    {
        if ($this->hasGlobalLocationAccess()) {
            return [];
        }

        if ($this->effectiveAccessScope() === self::ACCESS_SCOPE_SINGLE) {
            $activeLocationId = $this->resolveActiveLocationId();

            return $activeLocationId !== null ? [$activeLocationId] : [];
        }

        /** @var Collection<int, int> $locationIds */
        $locationIds = $this->allowedLocations()
            ->pluck('locations.id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($this->location_id !== null) {
            $locationIds->push((int) $this->location_id);
        }
        if ($this->active_location_id !== null) {
            $locationIds->push((int) $this->active_location_id);
        }

        return $locationIds->unique()->values()->all();
    }
}
