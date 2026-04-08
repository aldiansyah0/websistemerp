<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code', 30)->unique();
            $table->string('full_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 40)->nullable();
            $table->string('department', 80);
            $table->string('position_title', 120);
            $table->enum('employment_type', ['permanent', 'contract', 'part_time', 'intern'])->default('permanent');
            $table->date('join_date');
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('overtime_rate', 12, 2)->default(0);
            $table->enum('status', ['active', 'leave', 'resigned'])->default('active');
            $table->string('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
