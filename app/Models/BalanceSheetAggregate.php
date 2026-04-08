<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceSheetAggregate extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'report_date',
        'cash_movement',
        'accounts_receivable_movement',
        'inventory_movement',
        'accounts_payable_movement',
        'payroll_payable_movement',
        'equity_movement',
        'retained_earnings_movement',
        'journal_entries_count',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'cash_movement' => 'decimal:2',
            'accounts_receivable_movement' => 'decimal:2',
            'inventory_movement' => 'decimal:2',
            'accounts_payable_movement' => 'decimal:2',
            'payroll_payable_movement' => 'decimal:2',
            'equity_movement' => 'decimal:2',
            'retained_earnings_movement' => 'decimal:2',
            'journal_entries_count' => 'integer',
        ];
    }
}
