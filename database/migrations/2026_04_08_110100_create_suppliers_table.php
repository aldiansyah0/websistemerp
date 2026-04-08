<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('lead_time_days')->default(3);
            $table->unsignedInteger('payment_term_days')->default(14);
            $table->decimal('fill_rate', 5, 2)->default(0);
            $table->decimal('reject_rate', 5, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(4.0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
