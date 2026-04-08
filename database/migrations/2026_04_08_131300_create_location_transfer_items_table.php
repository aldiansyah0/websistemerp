<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('location_transfer_items')) {
            $this->ensureCompositeIndex();

            return;
        }

        Schema::create('location_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->decimal('requested_quantity', 12, 2);
            $table->decimal('sent_quantity', 12, 2)->default(0);
            $table->decimal('received_quantity', 12, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('location_transfer_id');
            $table->index('product_variant_id');
            $table->index(['location_transfer_id', 'product_variant_id'], 'idx_lti_transfer_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_transfer_items');
    }

    private function ensureCompositeIndex(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if ($this->hasCompositeTransferVariantIndex()) {
            return;
        }

        Schema::table('location_transfer_items', function (Blueprint $table): void {
            $table->index(['location_transfer_id', 'product_variant_id'], 'idx_lti_transfer_variant');
        });
    }

    private function hasCompositeTransferVariantIndex(): bool
    {
        $databaseName = DB::getDatabaseName();

        $rows = DB::table('information_schema.statistics')
            ->selectRaw('index_name, GROUP_CONCAT(column_name ORDER BY seq_in_index SEPARATOR \',\') as indexed_columns')
            ->where('table_schema', $databaseName)
            ->where('table_name', 'location_transfer_items')
            ->groupBy('index_name')
            ->get();

        foreach ($rows as $row) {
            if ((string) $row->indexed_columns === 'location_transfer_id,product_variant_id') {
                return true;
            }
        }

        return false;
    }
};
