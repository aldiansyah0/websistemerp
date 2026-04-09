<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addVariantColumn(
            table: 'inventory_ledgers',
            indexName: 'idx_il_variant_wh',
            compositeColumn: 'warehouse_id',
        );
        $this->addVariantColumn(
            table: 'purchase_order_items',
            indexName: 'idx_poi_po_variant',
            compositeColumn: 'purchase_order_id',
        );
        $this->addVariantColumn(
            table: 'sales_transaction_items',
            indexName: 'idx_sti_tx_variant',
            compositeColumn: 'sales_transaction_id',
        );
        $this->addVariantColumn(
            table: 'stock_transfer_items',
            indexName: 'idx_stfi_transfer_variant',
            compositeColumn: 'stock_transfer_id',
        );
        $this->addVariantColumn(
            table: 'goods_receipt_items',
            indexName: 'idx_gri_receipt_variant',
            compositeColumn: 'goods_receipt_id',
        );
        $this->addVariantColumn(
            table: 'stock_opname_items',
            indexName: 'idx_soi_opname_variant',
            compositeColumn: 'stock_opname_id',
        );
        $this->addVariantColumn(
            table: 'sales_return_items',
            indexName: 'idx_sri_return_variant',
            compositeColumn: 'sales_return_id',
        );
        $this->addVariantColumn(
            table: 'purchase_return_items',
            indexName: 'idx_pri_return_variant',
            compositeColumn: 'purchase_return_id',
        );

        $this->backfillVariantIds();
    }

    public function down(): void
    {
        $this->dropVariantColumn('inventory_ledgers', 'idx_il_variant_wh');
        $this->dropVariantColumn('purchase_order_items', 'idx_poi_po_variant');
        $this->dropVariantColumn('sales_transaction_items', 'idx_sti_tx_variant');
        $this->dropVariantColumn('stock_transfer_items', 'idx_stfi_transfer_variant');
        $this->dropVariantColumn('goods_receipt_items', 'idx_gri_receipt_variant');
        $this->dropVariantColumn('stock_opname_items', 'idx_soi_opname_variant');
        $this->dropVariantColumn('sales_return_items', 'idx_sri_return_variant');
        $this->dropVariantColumn('purchase_return_items', 'idx_pri_return_variant');
    }

    private function addVariantColumn(string $table, string $indexName, string $compositeColumn): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'product_variant_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName, $compositeColumn): void {
            $tableBlueprint->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();
            $tableBlueprint->index([$compositeColumn, 'product_variant_id'], $indexName);
        });
    }

    private function dropVariantColumn(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'product_variant_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName): void {
            $tableBlueprint->dropIndex($indexName);
            $tableBlueprint->dropConstrainedForeignId('product_variant_id');
        });
    }

    private function backfillVariantIds(): void
    {
        $variantMap = DB::table('product_variants')
            ->where('is_default', true)
            ->pluck('id', 'product_id')
            ->all();

        $tables = [
            'inventory_ledgers',
            'purchase_order_items',
            'sales_transaction_items',
            'stock_transfer_items',
            'goods_receipt_items',
            'stock_opname_items',
            'sales_return_items',
            'purchase_return_items',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'product_variant_id')) {
                continue;
            }

            DB::table($table)
                ->whereNull('product_variant_id')
                ->whereNotNull('product_id')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table, $variantMap): void {
                    foreach ($rows as $row) {
                        $variantId = $variantMap[(int) $row->product_id] ?? null;
                        if ($variantId === null) {
                            continue;
                        }

                        DB::table($table)
                            ->where('id', (int) $row->id)
                            ->update([
                                'product_variant_id' => (int) $variantId,
                                'updated_at' => now(),
                            ]);
                    }
                });
        }
    }
};

