<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PayrollRun extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['items.employee'];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'code',
        'period_start',
        'period_end',
        'status',
        'total_gross',
        'total_deductions',
        'total_net',
        'employee_count',
        'processed_at',
        'approved_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'total_gross' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_net' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollRunItem::class);
    }

    public function recalculateTotals(?Collection $items = null): void
    {
        $items = $items ?? $this->items;

        $this->employee_count = $items->count();
        $this->total_gross = $items->sum(fn (PayrollRunItem $item): float => (float) $item->base_salary + (float) $item->allowance_amount + (float) $item->overtime_amount + (float) $item->sales_bonus_amount);
        $this->total_deductions = $items->sum(fn (PayrollRunItem $item): float => (float) $item->deduction_amount);
        $this->total_net = $items->sum(fn (PayrollRunItem $item): float => (float) $item->net_salary);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAID => 'Paid',
        ];
    }
}
