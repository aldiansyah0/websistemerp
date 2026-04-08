<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class StockTransfer extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['sourceWarehouse', 'destinationWarehouse'];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'transfer_number',
        'source_warehouse_id',
        'destination_warehouse_id',
        'requested_by',
        'approved_by',
        'received_by',
        'request_date',
        'expected_receipt_date',
        'submitted_at',
        'approved_at',
        'received_at',
        'status',
        'total_quantity',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'expected_receipt_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
            'total_quantity' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function recalculateTotals(?Collection $items = null): void
    {
        $items = $items ?? $this->items;

        $this->total_quantity = $items->sum(fn (StockTransferItem $item): float => (float) $item->requested_quantity);
        $this->total_cost = $items->sum(fn (StockTransferItem $item): float => (float) $item->line_total);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING_APPROVAL, self::STATUS_REJECTED], true);
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING_APPROVAL, self::STATUS_APPROVED], true);
    }

    public function canBeReceived(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
