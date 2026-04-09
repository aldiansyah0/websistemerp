<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tax Configuration - Define tax rates for different transaction types
 *
 * @property int $id
 * @property int $tenant_id - Multi-tenant
 * @property string $tax_type - 'vat', 'service_tax', 'luxury_tax', 'excise', 'custom'
 * @property string $tax_name - e.g., "PPN 10%", "Bea Masuk 5%"
 * @property decimal|null $rate - Tax rate as percentage (e.g., 10 for 10%)
 * @property string $applicable_to - 'sales', 'purchases', 'both'
 * @property string|null $effective_from - When this tax rate becomes effective
 * @property string|null $effective_to - When this tax rate expires
 * @property bool $is_active
 * @property string|null $description
 * @property timestamps
 */
class TaxConfiguration extends Model
{
    protected $fillable = [
        'tenant_id',
        'tax_type',
        'tax_name',
        'rate',
        'applicable_to',
        'effective_from',
        'effective_to',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    // Tax types
    public const TYPE_VAT = 'vat';
    public const TYPE_SERVICE_TAX = 'service_tax';
    public const TYPE_LUXURY_TAX = 'luxury_tax';
    public const TYPE_EXCISE = 'excise';
    public const TYPE_CUSTOM = 'custom';

    // Applicable contexts
    public const APPLICABLE_SALES = 'sales';
    public const APPLICABLE_PURCHASES = 'purchases';
    public const APPLICABLE_BOTH = 'both';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeForSales($query)
    {
        return $query->whereIn('applicable_to', [self::APPLICABLE_SALES, self::APPLICABLE_BOTH]);
    }

    public function scopeForPurchases($query)
    {
        return $query->whereIn('applicable_to', [self::APPLICABLE_PURCHASES, self::APPLICABLE_BOTH]);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? today();

        return $query
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')
                    ->orWhereDate('effective_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            });
    }

    /**
     * Get effective tax rate for given date
     */
    public static function getRate(int $tenantId, string $taxType, $date = null): ?float
    {
        $config = self::where('tenant_id', $tenantId)
            ->where('tax_type', $taxType)
            ->active()
            ->effective($date)
            ->first();

        return $config?->rate ? (float) $config->rate : null;
    }

    /**
     * Calculate tax amount
     */
    public static function calculateTax(int $tenantId, string $taxType, float $baseAmount, $date = null): float
    {
        $rate = self::getRate($tenantId, $taxType, $date);

        return $rate ? ($baseAmount * $rate / 100) : 0;
    }
}
