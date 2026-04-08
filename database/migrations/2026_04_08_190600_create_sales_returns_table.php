<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreignId('sales_transaction_id')->constrained()->restrictOnDelete();
            $table->string('return_number', 60)->unique();
            $table->date('return_date');
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'location_id']);
            $table->index(['sales_transaction_id', 'status'], 'idx_sales_returns_transaction_status');
            $table->index('return_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};

