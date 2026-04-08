<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_type', 60);
            $table->string('format', 20);
            $table->json('filters')->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'location_id']);
            $table->index(['report_type', 'status']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};

