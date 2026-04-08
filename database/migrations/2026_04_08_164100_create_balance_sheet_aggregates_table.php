<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_sheet_aggregates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(0);
            $table->unsignedBigInteger('location_id')->default(0);
            $table->date('report_date');
            $table->decimal('cash_movement', 16, 2)->default(0);
            $table->decimal('accounts_receivable_movement', 16, 2)->default(0);
            $table->decimal('inventory_movement', 16, 2)->default(0);
            $table->decimal('accounts_payable_movement', 16, 2)->default(0);
            $table->decimal('payroll_payable_movement', 16, 2)->default(0);
            $table->decimal('equity_movement', 16, 2)->default(0);
            $table->decimal('retained_earnings_movement', 16, 2)->default(0);
            $table->unsignedInteger('journal_entries_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'location_id', 'report_date'], 'uniq_bs_aggregate_scope_date');
            $table->index(['tenant_id', 'report_date'], 'idx_bs_aggregate_tenant_date');
            $table->index(['location_id', 'report_date'], 'idx_bs_aggregate_location_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_sheet_aggregates');
    }
};
