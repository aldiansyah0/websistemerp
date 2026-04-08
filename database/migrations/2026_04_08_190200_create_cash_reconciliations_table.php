<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_reconciliations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('reconciliation_date');
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->decimal('expected_inflows', 16, 2)->default(0);
            $table->decimal('expected_outflows', 16, 2)->default(0);
            $table->decimal('expected_ending_balance', 16, 2)->default(0);
            $table->decimal('counted_ending_balance', 16, 2)->default(0);
            $table->decimal('difference_amount', 16, 2)->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'reconciliation_date'], 'uq_cash_reconciliation_loc_date');
            $table->index(['tenant_id', 'location_id', 'status'], 'idx_cash_reconciliation_scope_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
    }
};

