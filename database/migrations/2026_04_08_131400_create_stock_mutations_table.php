<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('related_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('transfer_id')->nullable()->constrained('location_transfers')->nullOnDelete();
            $table->enum('mutation_type', ['in', 'out', 'transfer', 'adjustment']);
            $table->enum('transfer_status', ['sent', 'in_transit', 'received'])->nullable();
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index('product_variant_id');
            $table->index('location_id');
            $table->index('related_location_id');
            $table->index('transfer_id');
            $table->index(['product_variant_id', 'location_id', 'occurred_at'], 'idx_stock_mutations_variant_location_time');
            $table->index(['reference_type', 'reference_id']);
            $table->index(['mutation_type', 'transfer_status']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
