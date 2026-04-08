<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreignId('transaction_id')->constrained('sales_transactions')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('reference_number')->nullable();
            $table->string('approval_code')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'location_id']);
            $table->index(['transaction_id', 'payment_method_id']);
            $table->index('settled_at');
        });

        if (Schema::hasTable('sales_payments')) {
            DB::table('sales_payments')
                ->orderBy('id')
                ->chunk(200, function ($rows): void {
                    $payload = [];

                    foreach ($rows as $row) {
                        $transaction = DB::table('sales_transactions')
                            ->select('tenant_id', 'location_id')
                            ->where('id', $row->sales_transaction_id)
                            ->first();

                        $payload[] = [
                            'id' => $row->id,
                            'tenant_id' => $transaction?->tenant_id,
                            'location_id' => $transaction?->location_id,
                            'transaction_id' => $row->sales_transaction_id,
                            'payment_method_id' => $row->payment_method_id,
                            'amount' => $row->amount,
                            'reference_number' => $row->reference_number,
                            'approval_code' => $row->approval_code,
                            'settled_at' => $row->settled_at,
                            'created_at' => $row->created_at,
                            'updated_at' => $row->updated_at,
                        ];
                    }

                    if ($payload !== []) {
                        DB::table('transaction_payments')->upsert(
                            $payload,
                            ['id'],
                            [
                                'tenant_id',
                                'location_id',
                                'transaction_id',
                                'payment_method_id',
                                'amount',
                                'reference_number',
                                'approval_code',
                                'settled_at',
                                'updated_at',
                            ]
                        );
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
