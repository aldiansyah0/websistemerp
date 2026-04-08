<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 120);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_overnight')->default(false);
            $table->unsignedSmallInteger('grace_minutes')->default(5);
            $table->unsignedSmallInteger('max_overtime_minutes')->default(240);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['location_id', 'is_active']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
