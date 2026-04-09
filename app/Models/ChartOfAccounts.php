<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Chart of Accounts Master
 * Standard Indonesian accounting structure (SKA - Standar Kerangka Akun)
 *
 * @property int $id
 * @property string $account_code - Unique account code (e.g., "1-1010" for cash)
 * @property string $account_name - Account name
 * @property string $account_type - 'asset', 'liability', 'equity', 'revenue', 'expense'
 * @property string $account_class - 'current_asset', 'fixed_asset', 'current_liability', 'long_term_liability', 'equity', 'operating_revenue', 'non_operating_revenue', 'operating_expense', 'non_operating_expense', etc.
 * @property int|null $parent_id - Parent account for hierarchy
 * @property bool $is_header - Whether this is a header account (no transactions, aggregates subs)
 * @property bool $is_active
 * @property string|null $description
 * @property timestamps
 */
class ChartOfAccounts extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'account_class',
        'parent_id',
        'is_header',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Account types
    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    // Account classes
    public const CLASS_CURRENT_ASSET = 'current_asset';
    public const CLASS_FIXED_ASSET = 'fixed_asset';
    public const CLASS_OTHER_ASSET = 'other_asset';
    public const CLASS_CURRENT_LIABILITY = 'current_liability';
    public const CLASS_LONG_TERM_LIABILITY = 'long_term_liability';
    public const CLASS_EQUITY = 'equity';
    public const CLASS_OPERATING_REVENUE = 'operating_revenue';
    public const CLASS_NON_OPERATING_REVENUE = 'non_operating_revenue';
    public const CLASS_OPERATING_EXPENSE = 'operating_expense';
    public const CLASS_NON_OPERATING_EXPENSE = 'non_operating_expense';
    public const CLASS_TAX_EXPENSE = 'tax_expense';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'gl_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    public function scopeByClass($query, string $class)
    {
        return $query->where('account_class', $class);
    }

    public function scopeHeaders($query)
    {
        return $query->where('is_header', true);
    }

    public function scopeLeaves($query)
    {
        return $query->where('is_header', false);
    }
}
