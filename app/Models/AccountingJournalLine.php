<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingJournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'gl_account_id',
        'line_no',
        'account_code',
        'account_name',
        'debit',
        'credit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(AccountingJournalEntry::class, 'journal_entry_id');
    }

    public function glAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccounts::class, 'gl_account_id');
    }
}
