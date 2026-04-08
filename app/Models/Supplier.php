<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'city',
        'address',
        'lead_time_days',
        'payment_term_days',
        'fill_rate',
        'reject_rate',
        'rating',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fill_rate' => 'decimal:2',
            'reject_rate' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'primary_supplier_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}
