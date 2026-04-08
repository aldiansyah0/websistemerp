<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('supplier_invoice_number', 100)->nullable()->after('po_number');
            $table->date('due_date')->nullable()->after('expected_date');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('status');
            $table->decimal('paid_amount', 14, 2)->default(0)->after('payment_status');
            $table->decimal('balance_due', 14, 2)->default(0)->after('paid_amount');
        });

        DB::table('purchase_orders')->update([
            'due_date' => DB::raw('COALESCE(expected_date, order_date)'),
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'balance_due' => DB::raw('total_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_invoice_number',
                'due_date',
                'payment_status',
                'paid_amount',
                'balance_due',
            ]);
        });
    }
};
