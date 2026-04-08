<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table): void {
            $table->decimal('refunded_amount', 14, 2)->default(0)->after('balance_due');
            $table->timestamp('last_refunded_at')->nullable()->after('refunded_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table): void {
            $table->dropColumn(['refunded_amount', 'last_refunded_at']);
        });
    }
};

