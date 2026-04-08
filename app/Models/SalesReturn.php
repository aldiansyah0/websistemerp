<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class SalesReturn extends Model
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
        'sales_transaction_id',
        'return_number',
        'return_date',
        'status',
        'refund_amount',
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
            'sales_transaction_id' => 'integer',
            'return_date' => 'date',
            'refund_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function recalculateTotals(?Collection $items = null): void
    {
        $rows = $items ?? $this->items;
        $this->refund_amount = (float) $rows->sum('line_total');
    }
}

