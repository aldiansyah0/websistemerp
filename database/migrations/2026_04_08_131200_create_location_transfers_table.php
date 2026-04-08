<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('request_date');
            $table->date('expected_receipt_date')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['draft', 'approved', 'sent', 'in_transit', 'received', 'cancelled', 'rejected'])->default('draft');
            $table->decimal('total_quantity', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('source_location_id');
            $table->index('destination_location_id');
            $table->index('status');
            $table->index('request_date');
            $table->index(['status', 'expected_receipt_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_transfers');
    }
};
