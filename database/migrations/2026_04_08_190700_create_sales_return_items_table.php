<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_transaction_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->string('reason', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sales_return_id', 'product_id']);
            $table->index(['sales_transaction_item_id'], 'idx_sales_return_items_sales_line');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};

