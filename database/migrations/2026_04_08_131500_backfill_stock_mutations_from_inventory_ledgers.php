<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_mutations')
            || ! Schema::hasTable('inventory_ledgers')
            || ! Schema::hasTable('product_variants')
            || ! Schema::hasTable('locations')) {
            return;
        }

        $variantMap = DB::table('product_variants')
            ->where('is_default', true)
            ->pluck('id', 'product_id');
        $locationMap = DB::table('locations')
            ->where('type', 'warehouse')
            ->whereNotNull('legacy_warehouse_id')
            ->pluck('id', 'legacy_warehouse_id');

        if ($variantMap->isEmpty() || $locationMap->isEmpty()) {
            return;
        }

        DB::table('inventory_ledgers')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($variantMap, $locationMap): void {
                $payload = [];
                $now = now();

                foreach ($rows as $row) {
                    $variantId = $variantMap[(int) $row->product_id] ?? null;
                    $locationId = $locationMap[(int) $row->warehouse_id] ?? null;

                    if ($variantId === null || $locationId === null) {
                        continue;
                    }

                    [$mutationType, $quantity] = $this->mapMovement((string) $row->movement_type, (float) $row->quantity);

                    $payload[] = [
                        'product_variant_id' => $variantId,
                        'location_id' => $locationId,
                        'mutation_type' => $mutationType,
                        'quantity' => $quantity,
                        'unit_cost' => (float) $row->unit_cost,
                        'balance_after' => $row->balance_after !== null ? (float) $row->balance_after : null,
                        'reference_type' => $row->reference_type ? (string) $row->reference_type : null,
                        'reference_id' => $row->reference_id !== null ? (int) $row->reference_id : null,
                        'notes' => $row->notes ? (string) $row->notes : null,
                        'occurred_at' => $row->transaction_at,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($payload !== []) {
                    DB::table('stock_mutations')->insert($payload);
                }
            });
    }

    public function down(): void
    {
        // Intentionally left blank to avoid deleting legitimate runtime mutations on rollback.
    }

    private function mapMovement(string $movementType, float $quantity): array
    {
        return match ($movementType) {
            'opening', 'purchase', 'transfer_in', 'return_from_customer' => ['in', abs($quantity)],
            'sale', 'transfer_out', 'return_to_supplier' => ['out', abs($quantity)],
            'adjustment', 'cycle_count' => ['adjustment', abs($quantity)],
            default => $quantity >= 0 ? ['in', abs($quantity)] : ['out', abs($quantity)],
        };
    }
};
