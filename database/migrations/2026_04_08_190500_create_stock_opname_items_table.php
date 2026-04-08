<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('system_quantity', 14, 2)->default(0);
            $table->decimal('physical_quantity', 14, 2)->default(0);
            $table->decimal('variance_quantity', 14, 2)->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('variance_value', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_opname_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};

