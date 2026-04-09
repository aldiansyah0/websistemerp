<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    public const OWNER = 'owner';
    public const MANAGER = 'manager';
    public const HRD = 'hrd';
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const STAFF_ADMIN = 'staff_admin';
    public const CASHIER = 'cashier';
    public const STAFF_OUTLET = 'staff_outlet';
    public const WAREHOUSE_MANAGER = 'warehouse_manager';
    public const FINANCE = 'finance';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
