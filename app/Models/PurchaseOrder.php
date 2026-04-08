<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PurchaseOrder extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['supplier', 'warehouse'];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'po_number',
        'supplier_invoice_number',
        'supplier_id',
        'warehouse_id',
        'created_by',
        'approved_by',
        'order_date',
        'expected_date',
        'due_date',
        'submitted_at',
        'approved_at',
        'received_at',
        'status',
        'payment_status',
        'paid_amount',
        'balance_due',
        'subtotal',
        'tax_amount',
        'total_amount',
        'terms',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'due_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchaseOrderPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function recalculateTotals(?Collection $items = null): void
    {
        $items = $items ?? $this->items;

        $subtotal = $items->sum(fn(PurchaseOrderItem $item): float => (float) $item->line_total);

        $this->subtotal = $subtotal;
        $this->tax_amount = 0;
        $this->total_amount = $subtotal;
        $this->balance_due = max((float) $this->total_amount - (float) $this->paid_amount, 0);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_REJECTED,
        ], true);
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_APPROVED,
        ], true);
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_RECEIVED,
        ], true);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'unpaid' => 'Unpaid',
            'partial' => 'Partial',
            'paid' => 'Paid',
        ];
    }
}
