<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('payment_date');
            $table->decimal('amount', 14, 2);
            $table->string('reference_number', 100)->nullable();
            $table->string('approval_code', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payment_date', 'purchase_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_payments');
    }
};
