<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Account Mapping - Maps transaction types to GL accounts
 * Used for automatic journal entry posting
 *
 * @property int $id
 * @property int $tenant_id - Tenant who owns this mapping
 * @property string $transaction_type - Type: 'purchase_order', 'goods_receipt', 'sales_transaction', 'purchase_return', 'sales_return', 'payroll', 'cash_reconciliation', etc.
 * @property string $mapping_key - Detailed key: 'po_inventory', 'po_expense', 'gr_inventory', 'gr_supplier', 'sales_revenue', 'sales_cogs', 'sales_ar', 'pr_inventory', etc.
 * @property int $gl_account_id - Chart of Accounts ID
 * @property string|null $description - Purpose of this mapping
 * @property bool $is_active
 * @property timestamps
 */
class AccountMapping extends Model
{
    protected $fillable = [
        'tenant_id',
        'transaction_type',
        'mapping_key',
        'gl_account_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Transaction types
    public const TYPE_PURCHASE_ORDER = 'purchase_order';
    public const TYPE_GOODS_RECEIPT = 'goods_receipt';
    public const TYPE_SALES_TRANSACTION = 'sales_transaction';
    public const TYPE_SALES_RETURN = 'sales_return';
    public const TYPE_PURCHASE_RETURN = 'purchase_return';
    public const TYPE_PAYROLL = 'payroll';
    public const TYPE_CASH_RECONCILIATION = 'cash_reconciliation';
    public const TYPE_STOCK_TRANSFER = 'stock_transfer';
    public const TYPE_STOCK_OPNAME = 'stock_opname';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccounts::class, 'gl_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('mapping_key', $key);
    }

    /**
     * Get the GL account code for a specific mapping
     */
    public static function getAccountCode(int $tenantId, string $transactionType, string $mappingKey): ?string
    {
        $mapping = self::where('tenant_id', $tenantId)
            ->where('transaction_type', $transactionType)
            ->where('mapping_key', $mappingKey)
            ->active()
            ->with('glAccount')
            ->first();

        return $mapping?->glAccount?->account_code;
    }

    /**
     * Get the GL account for a specific mapping
     */
    public static function getAccount(int $tenantId, string $transactionType, string $mappingKey): ?ChartOfAccounts
    {
        return self::where('tenant_id', $tenantId)
            ->where('transaction_type', $transactionType)
            ->where('mapping_key', $mappingKey)
            ->active()
            ->with('glAccount')
            ->first()?->glAccount;
    }
}
