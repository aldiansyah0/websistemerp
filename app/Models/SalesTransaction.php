<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenantLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class SalesTransaction extends Model
{
    use HasFactory;
    use BelongsToTenantLocation;

    protected bool $locationScoped = true;

    protected $with = ['outlet', 'customer'];

    protected $fillable = [
        'tenant_id',
        'location_id',
        'outlet_id',
        'cashier_employee_id',
        'customer_id',
        'transaction_number',
        'invoice_number',
        'sold_at',
        'invoice_date',
        'due_date',
        'gross_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'split_payment_count',
        'items_count',
        'status',
        'payment_status',
        'paid_amount',
        'balance_due',
        'refunded_amount',
        'last_refunded_at',
        'customer_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'gross_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'last_refunded_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'cashier_employee_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalesPayment::class, 'transaction_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function recalculateSettlement(?Collection $payments = null): void
    {
        $payments = $payments ?? $this->payments;
        $paidAmount = (float) $payments->sum('amount');
        $balanceDue = max((float) $this->net_amount - $paidAmount, 0);

        $this->paid_amount = $paidAmount;
        $this->balance_due = $balanceDue;
        $this->split_payment_count = $payments->count();
        $this->payment_status = match (true) {
            $balanceDue <= 0.0001 => 'paid',
            $paidAmount > 0 => 'partial',
            default => 'unpaid',
        };
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
