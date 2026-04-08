<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->string('reference_type', 120)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('total_debit', 16, 2)->default(0);
            $table->decimal('total_credit', 16, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'location_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_entries');
    }
};
