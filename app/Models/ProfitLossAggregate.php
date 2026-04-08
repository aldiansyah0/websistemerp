<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitLossAggregate extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'report_date',
        'revenue_amount',
        'cogs_amount',
        'payroll_expense_amount',
        'operating_expense_amount',
        'other_income_amount',
        'net_profit_amount',
        'journal_entries_count',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'revenue_amount' => 'decimal:2',
            'cogs_amount' => 'decimal:2',
            'payroll_expense_amount' => 'decimal:2',
            'operating_expense_amount' => 'decimal:2',
            'other_income_amount' => 'decimal:2',
            'net_profit_amount' => 'decimal:2',
            'journal_entries_count' => 'integer',
        ];
    }
}
