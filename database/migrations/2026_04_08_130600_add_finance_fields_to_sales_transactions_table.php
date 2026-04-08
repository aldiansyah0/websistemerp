<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('cashier_employee_id')->constrained()->nullOnDelete();
            $table->string('invoice_number', 50)->nullable()->unique()->after('transaction_number');
            $table->date('invoice_date')->nullable()->after('sold_at');
            $table->date('due_date')->nullable()->after('invoice_date');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('paid')->after('status');
            $table->decimal('paid_amount', 14, 2)->default(0)->after('payment_status');
            $table->decimal('balance_due', 14, 2)->default(0)->after('paid_amount');
        });

        DB::table('sales_transactions')->update([
            'invoice_number' => DB::raw('transaction_number'),
            'invoice_date' => DB::raw('date(sold_at)'),
            'due_date' => DB::raw('date(sold_at)'),
            'payment_status' => 'paid',
            'paid_amount' => DB::raw('net_amount'),
            'balance_due' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropUnique(['invoice_number']);
            $table->dropColumn([
                'invoice_number',
                'invoice_date',
                'due_date',
                'payment_status',
                'paid_amount',
                'balance_due',
            ]);
        });
    }
};
