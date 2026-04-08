<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('accounting_journal_entries')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->string('account_code', 30);
            $table->string('account_name', 120);
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'line_no']);
            $table->index('account_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_lines');
    }
};
