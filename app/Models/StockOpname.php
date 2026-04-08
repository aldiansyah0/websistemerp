<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class StockOpname extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'warehouse_id',
        'opname_number',
        'opname_date',
        'status',
        'total_items',
        'total_variance_qty',
        'total_variance_value',
        'submitted_at',
        'approved_at',
        'approved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'location_id' => 'integer',
            'opname_date' => 'date',
            'total_variance_qty' => 'decimal:2',
            'total_variance_value' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function recalculateTotals(?Collection $items = null): void
    {
        $rows = $items ?? $this->items;
        $this->total_items = $rows->count();
        $this->total_variance_qty = (float) $rows->sum('variance_quantity');
        $this->total_variance_value = (float) $rows->sum('variance_value');
    }
}

