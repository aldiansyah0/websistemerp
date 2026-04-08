<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('opname_number', 60)->unique();
            $table->date('opname_date');
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
            $table->unsignedInteger('total_items')->default(0);
            $table->decimal('total_variance_qty', 14, 2)->default(0);
            $table->decimal('total_variance_value', 16, 2)->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'location_id']);
            $table->index(['warehouse_id', 'opname_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};

