<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('region', 60)->nullable();
            $table->string('city', 80);
            $table->string('phone', 40)->nullable();
            $table->string('manager_name')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->date('opening_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'renovation'])->default('active');
            $table->decimal('daily_sales_target', 14, 2)->default(0);
            $table->decimal('service_level', 5, 2)->default(0);
            $table->decimal('inventory_accuracy', 5, 2)->default(0);
            $table->boolean('is_fulfillment_hub')->default(false);
            $table->text('address')->nullable();
            $table->timestamps();

            $table->index(['status', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
