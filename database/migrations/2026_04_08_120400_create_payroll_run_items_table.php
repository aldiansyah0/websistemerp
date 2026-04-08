<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('allowance_amount', 14, 2)->default(0);
            $table->decimal('overtime_amount', 14, 2)->default(0);
            $table->decimal('deduction_amount', 14, 2)->default(0);
            $table->decimal('net_salary', 14, 2)->default(0);
            $table->enum('payment_status', ['pending', 'approved', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_items');
    }
};
