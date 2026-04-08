<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->enum('movement_type', [
                'opening',
                'purchase',
                'sale',
                'transfer_in',
                'transfer_out',
                'adjustment',
                'return_to_supplier',
                'return_from_customer',
                'cycle_count',
            ]);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('transaction_at');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('transaction_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledgers');
    }
};
