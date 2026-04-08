<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (! Schema::hasColumn('employees', 'sales_bonus_rate')) {
                $table->decimal('sales_bonus_rate', 6, 3)->default(0)->after('base_salary');
            }

            if (! Schema::hasColumn('employees', 'late_penalty_per_minute')) {
                $table->decimal('late_penalty_per_minute', 12, 2)->default(0)->after('overtime_rate');
            }

            if (! Schema::hasColumn('employees', 'absence_penalty_amount')) {
                $table->decimal('absence_penalty_amount', 12, 2)->default(0)->after('late_penalty_per_minute');
            }
        });

        if (Schema::hasColumn('employees', 'location_id') && Schema::hasColumn('employees', 'outlet_id')) {
            $outletLocationMap = DB::table('outlets')
                ->whereNotNull('location_id')
                ->pluck('location_id', 'id');

            $employees = DB::table('employees')
                ->select('id', 'outlet_id')
                ->whereNull('location_id')
                ->whereNotNull('outlet_id')
                ->get();

            foreach ($employees as $employee) {
                $locationId = $outletLocationMap[(int) $employee->outlet_id] ?? null;

                if ($locationId !== null) {
                    DB::table('employees')
                        ->where('id', $employee->id)
                        ->update(['location_id' => (int) $locationId]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (Schema::hasColumn('employees', 'absence_penalty_amount')) {
                $table->dropColumn('absence_penalty_amount');
            }

            if (Schema::hasColumn('employees', 'late_penalty_per_minute')) {
                $table->dropColumn('late_penalty_per_minute');
            }

            if (Schema::hasColumn('employees', 'sales_bonus_rate')) {
                $table->dropColumn('sales_bonus_rate');
            }
        });
    }
};
