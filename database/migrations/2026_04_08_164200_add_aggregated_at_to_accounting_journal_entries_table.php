<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_journal_entries', function (Blueprint $table): void {
            if (! Schema::hasColumn('accounting_journal_entries', 'aggregated_at')) {
                $table->timestamp('aggregated_at')->nullable()->after('total_credit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_journal_entries', function (Blueprint $table): void {
            if (Schema::hasColumn('accounting_journal_entries', 'aggregated_at')) {
                $table->dropColumn('aggregated_at');
            }
        });
    }
};
