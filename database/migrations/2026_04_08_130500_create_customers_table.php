<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('segment', 50)->default('Retail');
            $table->string('city', 120)->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->unsignedSmallInteger('payment_term_days')->default(0);
            $table->enum('status', ['prospect', 'active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'segment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
