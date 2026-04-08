<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, bool>
     */
    private array $tables = [
        'users' => true,
        'categories' => false,
        'suppliers' => false,
        'products' => false,
        'product_variants' => false,
        'warehouses' => true,
        'outlets' => true,
        'locations' => false,
        'inventory_ledgers' => true,
        'purchase_orders' => true,
        'goods_receipts' => true,
        'sales_transactions' => true,
        'stock_transfers' => true,
        'stock_mutations' => false,
        'employees' => true,
        'attendance_logs' => true,
        'payroll_runs' => true,
        'payment_methods' => false,
        'customers' => true,
        'location_transfers' => false,
    ];

    public function up(): void
    {
        $tenantId = $this->ensureDefaultTenant();

        $this->addScopeColumns();
        $this->backfillScopeColumns($tenantId);
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $includeLocationColumn) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $includeLocationColumn): void {
                if ($includeLocationColumn && Schema::hasColumn($table, 'location_id')) {
                    $blueprint->dropColumn('location_id');
                }

                if (Schema::hasColumn($table, 'tenant_id')) {
                    $blueprint->dropColumn('tenant_id');
                }
            });
        }

        Schema::dropIfExists('tenants');
    }

    private function ensureDefaultTenant(): int
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'code']);
            });
        }

        $existing = DB::table('tenants')->where('code', 'default')->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return (int) DB::table('tenants')->insertGetId([
            'code' => 'default',
            'name' => 'Default Tenant',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function addScopeColumns(): void
    {
        foreach ($this->tables as $table => $includeLocationColumn) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $includeLocationColumn): void {
                if (! Schema::hasColumn($table, 'tenant_id')) {
                    $blueprint->unsignedBigInteger('tenant_id')->nullable()->index();
                }

                if ($includeLocationColumn && ! Schema::hasColumn($table, 'location_id')) {
                    $blueprint->unsignedBigInteger('location_id')->nullable()->index();
                }
            });
        }
    }

    private function backfillScopeColumns(int $tenantId): void
    {
        foreach (array_keys($this->tables) as $table) {
            $this->updateTenantForTable($table, $tenantId);
        }

        $warehouseLocationMap = $this->warehouseLocationMap();
        $outletLocationMap = $this->outletLocationMap();
        $fallbackLocationId = $this->firstLocationId($warehouseLocationMap, $outletLocationMap);

        $this->backfillUsers($tenantId, $fallbackLocationId);
        $this->backfillFromWarehouse('warehouses', 'id', $warehouseLocationMap, $tenantId);
        $this->backfillFromOutlet('outlets', 'id', $outletLocationMap, $tenantId);
        $this->backfillFromWarehouse('inventory_ledgers', 'warehouse_id', $warehouseLocationMap, $tenantId);
        $this->backfillFromWarehouse('purchase_orders', 'warehouse_id', $warehouseLocationMap, $tenantId);
        $this->backfillFromWarehouse('goods_receipts', 'warehouse_id', $warehouseLocationMap, $tenantId);
        $this->backfillFromOutlet('sales_transactions', 'outlet_id', $outletLocationMap, $tenantId);
        $this->backfillFromWarehouse('stock_transfers', 'source_warehouse_id', $warehouseLocationMap, $tenantId);
        $this->backfillFromOutlet('employees', 'outlet_id', $outletLocationMap, $tenantId);
        $this->backfillFromOutlet('attendance_logs', 'outlet_id', $outletLocationMap, $tenantId);
        $this->backfillPayrollRuns($tenantId, $fallbackLocationId);
    }

    private function warehouseLocationMap(): array
    {
        if (! Schema::hasTable('locations')) {
            return [];
        }

        return DB::table('locations')
            ->whereNotNull('legacy_warehouse_id')
            ->pluck('id', 'legacy_warehouse_id')
            ->map(fn ($value): int => (int) $value)
            ->all();
    }

    private function outletLocationMap(): array
    {
        if (! Schema::hasTable('locations')) {
            return [];
        }

        return DB::table('locations')
            ->whereNotNull('legacy_outlet_id')
            ->pluck('id', 'legacy_outlet_id')
            ->map(fn ($value): int => (int) $value)
            ->all();
    }

    private function firstLocationId(array $warehouseLocationMap, array $outletLocationMap): ?int
    {
        $warehouseFirst = $warehouseLocationMap !== [] ? reset($warehouseLocationMap) : null;
        if ($warehouseFirst !== false && $warehouseFirst !== null) {
            return (int) $warehouseFirst;
        }

        $outletFirst = $outletLocationMap !== [] ? reset($outletLocationMap) : null;
        if ($outletFirst !== false && $outletFirst !== null) {
            return (int) $outletFirst;
        }

        return null;
    }

    private function updateTenantForTable(string $table, int $tenantId): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        DB::table($table)
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);
    }

    private function backfillUsers(int $tenantId, ?int $locationId): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);

        if (Schema::hasColumn('users', 'location_id') && $locationId !== null) {
            DB::table('users')
                ->whereNull('location_id')
                ->update(['location_id' => $locationId]);
        }
    }

    private function backfillFromWarehouse(string $table, string $warehouseColumn, array $locationMap, int $tenantId): void
    {
        if (
            ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'location_id')
            || ! Schema::hasColumn($table, $warehouseColumn)
        ) {
            return;
        }

        $rows = DB::table($table)->select('id', $warehouseColumn)->get();

        foreach ($rows as $row) {
            $warehouseId = $row->{$warehouseColumn};
            $locationId = $warehouseId !== null ? ($locationMap[(int) $warehouseId] ?? null) : null;

            DB::table($table)
                ->where('id', $row->id)
                ->update([
                    'tenant_id' => $tenantId,
                    'location_id' => $locationId,
                ]);
        }
    }

    private function backfillFromOutlet(string $table, string $outletColumn, array $locationMap, int $tenantId): void
    {
        if (
            ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'location_id')
            || ! Schema::hasColumn($table, $outletColumn)
        ) {
            return;
        }

        $rows = DB::table($table)->select('id', $outletColumn)->get();

        foreach ($rows as $row) {
            $outletId = $row->{$outletColumn};
            $locationId = $outletId !== null ? ($locationMap[(int) $outletId] ?? null) : null;

            DB::table($table)
                ->where('id', $row->id)
                ->update([
                    'tenant_id' => $tenantId,
                    'location_id' => $locationId,
                ]);
        }
    }

    private function backfillPayrollRuns(int $tenantId, ?int $fallbackLocationId): void
    {
        if (! Schema::hasTable('payroll_runs')) {
            return;
        }

        $payload = ['tenant_id' => $tenantId];

        if (Schema::hasColumn('payroll_runs', 'location_id') && $fallbackLocationId !== null) {
            $payload['location_id'] = $fallbackLocationId;
        }

        DB::table('payroll_runs')->whereNull('tenant_id')->update($payload);
    }
};
