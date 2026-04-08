<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_run_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('payroll_run_items', 'sales_bonus_amount')) {
                $table->decimal('sales_bonus_amount', 14, 2)->default(0)->after('overtime_amount');
            }

            if (! Schema::hasColumn('payroll_run_items', 'attendance_deduction_amount')) {
                $table->decimal('attendance_deduction_amount', 14, 2)->default(0)->after('deduction_amount');
            }

            if (! Schema::hasColumn('payroll_run_items', 'late_deduction_amount')) {
                $table->decimal('late_deduction_amount', 14, 2)->default(0)->after('attendance_deduction_amount');
            }

            if (! Schema::hasColumn('payroll_run_items', 'absence_deduction_amount')) {
                $table->decimal('absence_deduction_amount', 14, 2)->default(0)->after('late_deduction_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_run_items', function (Blueprint $table): void {
            if (Schema::hasColumn('payroll_run_items', 'absence_deduction_amount')) {
                $table->dropColumn('absence_deduction_amount');
            }

            if (Schema::hasColumn('payroll_run_items', 'late_deduction_amount')) {
                $table->dropColumn('late_deduction_amount');
            }

            if (Schema::hasColumn('payroll_run_items', 'attendance_deduction_amount')) {
                $table->dropColumn('attendance_deduction_amount');
            }

            if (Schema::hasColumn('payroll_run_items', 'sales_bonus_amount')) {
                $table->dropColumn('sales_bonus_amount');
            }
        });
    }
};
