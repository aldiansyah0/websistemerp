<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_loss_aggregates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(0);
            $table->unsignedBigInteger('location_id')->default(0);
            $table->date('report_date');
            $table->decimal('revenue_amount', 16, 2)->default(0);
            $table->decimal('cogs_amount', 16, 2)->default(0);
            $table->decimal('payroll_expense_amount', 16, 2)->default(0);
            $table->decimal('operating_expense_amount', 16, 2)->default(0);
            $table->decimal('other_income_amount', 16, 2)->default(0);
            $table->decimal('net_profit_amount', 16, 2)->default(0);
            $table->unsignedInteger('journal_entries_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'location_id', 'report_date'], 'uniq_pl_aggregate_scope_date');
            $table->index(['tenant_id', 'report_date'], 'idx_pl_aggregate_tenant_date');
            $table->index(['location_id', 'report_date'], 'idx_pl_aggregate_location_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_loss_aggregates');
    }
};
