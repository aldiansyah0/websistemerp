<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 80)->nullable()->unique();
            $table->string('variant_name')->default('Default');
            $table->string('size', 40)->nullable();
            $table->string('color', 40)->nullable();
            $table->json('attributes')->nullable();
            $table->string('unit_of_measure', 30)->default('pcs');
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->decimal('reorder_quantity', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index(['product_id', 'is_default']);
            $table->index(['product_id', 'status']);
            $table->index('sku');
            $table->index('barcode');
        });

        $this->backfillDefaultVariants();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }

    private function backfillDefaultVariants(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $now = now();
        $products = DB::table('products')->orderBy('id')->get();

        foreach ($products as $product) {
            DB::table('product_variants')->insert([
                'product_id' => (int) $product->id,
                'sku' => (string) $product->sku,
                'barcode' => $product->barcode ? (string) $product->barcode : null,
                'variant_name' => 'Default',
                'unit_of_measure' => $product->unit_of_measure ? (string) $product->unit_of_measure : 'pcs',
                'cost_price' => (float) $product->cost_price,
                'selling_price' => (float) $product->selling_price,
                'reorder_level' => (float) $product->reorder_level,
                'reorder_quantity' => (float) $product->reorder_quantity,
                'status' => in_array($product->status, ['active', 'inactive', 'discontinued'], true) ? (string) $product->status : 'active',
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
