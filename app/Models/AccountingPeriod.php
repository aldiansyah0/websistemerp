<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = false;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'period_code',
        'start_date',
        'end_date',
        'status',
        'closed_at',
        'closed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'location_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}

