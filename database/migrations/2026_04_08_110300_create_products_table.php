<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('primary_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 80)->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('unit_of_measure', 30)->default('pcs');
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->decimal('daily_run_rate', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->decimal('reorder_quantity', 12, 2)->default(0);
            $table->unsignedInteger('shelf_life_days')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
