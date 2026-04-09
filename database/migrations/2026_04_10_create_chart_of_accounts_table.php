<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique()->comment('Account code e.g., 1-1010');
            $table->string('account_name')->comment('Account name');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->comment('Primary account type');
            $table->string('account_class')->comment('Detailed account class for reporting');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent account ID for hierarchy');
            $table->boolean('is_header')->default(false)->comment('True if header account (no direct posting)');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('account_code');
            $table->index('account_type');
            $table->index('account_class');
            $table->index('is_active');
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
