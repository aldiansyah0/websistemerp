<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationTransfer extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    protected $with = ['sourceLocation', 'destinationLocation'];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SENT = 'sent';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'transfer_number',
        'source_location_id',
        'destination_location_id',
        'requested_by',
        'approved_by',
        'sent_by',
        'received_by',
        'request_date',
        'expected_receipt_date',
        'sent_at',
        'in_transit_at',
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
            'sent_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'received_at' => 'datetime',
            'total_quantity' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LocationTransferItem::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class, 'transfer_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_SENT => 'Sent',
            self::STATUS_IN_TRANSIT => 'In-Transit',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }
}
