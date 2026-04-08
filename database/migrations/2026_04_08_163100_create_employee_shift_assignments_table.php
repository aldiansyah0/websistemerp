<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_shift_assignments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->date('shift_date');
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->dateTime('clock_in_at')->nullable();
            $table->dateTime('clock_out_at')->nullable();
            $table->enum('workflow_status', ['scheduled', 'checked_in', 'checked_out', 'closed', 'cancelled'])->default('scheduled');
            $table->enum('attendance_status', ['off', 'present', 'late', 'leave', 'absent'])->default('off');
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'shift_id', 'shift_date'], 'uniq_employee_shift_date');
            $table->index(['location_id', 'shift_date'], 'idx_shift_assignments_location_date');
            $table->index(['employee_id', 'shift_date'], 'idx_shift_assignments_employee_date');
            $table->index(['workflow_status', 'attendance_status'], 'idx_shift_assignments_workflow_attendance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift_assignments');
    }
};
