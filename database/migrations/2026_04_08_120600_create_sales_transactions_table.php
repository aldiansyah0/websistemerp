<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->restrictOnDelete();
            $table->foreignId('cashier_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('transaction_number', 50)->unique();
            $table->dateTime('sold_at');
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2)->default(0);
            $table->unsignedSmallInteger('split_payment_count')->default(1);
            $table->unsignedSmallInteger('items_count')->default(0);
            $table->enum('status', ['paid', 'refunded', 'void'])->default('paid');
            $table->string('customer_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sold_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transactions');
    }
};
