<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReconciliation extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'reconciliation_date',
        'opening_balance',
        'expected_inflows',
        'expected_outflows',
        'expected_ending_balance',
        'counted_ending_balance',
        'difference_amount',
        'status',
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
            'reconciliation_date' => 'date',
            'opening_balance' => 'decimal:2',
            'expected_inflows' => 'decimal:2',
            'expected_outflows' => 'decimal:2',
            'expected_ending_balance' => 'decimal:2',
            'counted_ending_balance' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

