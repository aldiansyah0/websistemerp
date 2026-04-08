<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'provider',
        'transaction_fee_rate',
        'settlement_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'transaction_fee_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function salesPayments(): HasMany
    {
        return $this->hasMany(SalesPayment::class);
    }

    public function purchaseOrderPayments(): HasMany
    {
        return $this->hasMany(PurchaseOrderPayment::class);
    }
}
