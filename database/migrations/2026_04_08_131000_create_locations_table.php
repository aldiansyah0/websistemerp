<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['outlet', 'warehouse']);
            $table->string('code', 40);
            $table->string('name');
            $table->string('warehouse_subtype', 40)->nullable();
            $table->string('region', 60)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('manager_name')->nullable();
            $table->date('opening_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'renovation'])->default('active');
            $table->boolean('is_fulfillment_hub')->default(false);
            $table->text('address')->nullable();
            $table->foreignId('parent_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('fulfillment_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->unsignedBigInteger('legacy_warehouse_id')->nullable()->unique();
            $table->unsignedBigInteger('legacy_outlet_id')->nullable()->unique();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['type', 'code']);
            $table->index('code');
            $table->index(['type', 'status']);
            $table->index('city');
            $table->index('parent_location_id');
            $table->index('fulfillment_location_id');
        });

        $this->backfillWarehouses();
        $this->backfillOutlets();
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }

    private function backfillWarehouses(): void
    {
        if (! Schema::hasTable('warehouses')) {
            return;
        }

        $now = now();
        $warehouses = DB::table('warehouses')->orderBy('id')->get();

        foreach ($warehouses as $warehouse) {
            DB::table('locations')->insert([
                'type' => 'warehouse',
                'code' => (string) $warehouse->code,
                'name' => (string) $warehouse->name,
                'warehouse_subtype' => $warehouse->type ? (string) $warehouse->type : null,
                'city' => $warehouse->city ? (string) $warehouse->city : null,
                'address' => $warehouse->address ? (string) $warehouse->address : null,
                'status' => (bool) $warehouse->is_active ? 'active' : 'inactive',
                'is_fulfillment_hub' => false,
                'legacy_warehouse_id' => (int) $warehouse->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function backfillOutlets(): void
    {
        if (! Schema::hasTable('outlets')) {
            return;
        }

        $warehouseLocationMap = DB::table('locations')
            ->where('type', 'warehouse')
            ->whereNotNull('legacy_warehouse_id')
            ->pluck('id', 'legacy_warehouse_id');
        $now = now();
        $outlets = DB::table('outlets')->orderBy('id')->get();

        foreach ($outlets as $outlet) {
            $linkedLocationId = null;
            if ($outlet->warehouse_id !== null) {
                $linkedLocationId = $warehouseLocationMap[(int) $outlet->warehouse_id] ?? null;
            }

            DB::table('locations')->insert([
                'type' => 'outlet',
                'code' => (string) $outlet->code,
                'name' => (string) $outlet->name,
                'region' => $outlet->region ? (string) $outlet->region : null,
                'city' => $outlet->city ? (string) $outlet->city : null,
                'phone' => $outlet->phone ? (string) $outlet->phone : null,
                'manager_name' => $outlet->manager_name ? (string) $outlet->manager_name : null,
                'opening_date' => $outlet->opening_date,
                'status' => in_array($outlet->status, ['active', 'inactive', 'renovation'], true) ? (string) $outlet->status : 'active',
                'is_fulfillment_hub' => (bool) $outlet->is_fulfillment_hub,
                'address' => $outlet->address ? (string) $outlet->address : null,
                'fulfillment_location_id' => $linkedLocationId,
                'legacy_outlet_id' => (int) $outlet->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
