<?php

use App\Models\Location;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed();
});

test('global tenant and location scope limits outlet query automatically', function () {
    $tenantA = Tenant::query()->where('code', 'default')->firstOrFail();
    $tenantB = Tenant::query()->updateOrCreate(
        ['code' => 'tenant-b'],
        ['name' => 'Tenant B', 'is_active' => true]
    );

    $locationA = Location::query()->create([
        'tenant_id' => $tenantA->id,
        'type' => Location::TYPE_OUTLET,
        'code' => 'LOC-A-' . Str::upper(Str::random(5)),
        'name' => 'Location A',
        'status' => 'active',
    ]);
    $locationB = Location::query()->create([
        'tenant_id' => $tenantA->id,
        'type' => Location::TYPE_OUTLET,
        'code' => 'LOC-B-' . Str::upper(Str::random(5)),
        'name' => 'Location B',
        'status' => 'active',
    ]);
    $locationC = Location::query()->create([
        'tenant_id' => $tenantB->id,
        'type' => Location::TYPE_OUTLET,
        'code' => 'LOC-C-' . Str::upper(Str::random(5)),
        'name' => 'Location C',
        'status' => 'active',
    ]);

    $outletA = Outlet::query()->create([
        'tenant_id' => $tenantA->id,
        'location_id' => $locationA->id,
        'code' => 'SCOPE-A-' . Str::upper(Str::random(4)),
        'name' => 'Outlet Scope A',
        'city' => 'Jakarta',
        'status' => Outlet::STATUS_ACTIVE,
    ]);
    $outletB = Outlet::query()->create([
        'tenant_id' => $tenantA->id,
        'location_id' => $locationB->id,
        'code' => 'SCOPE-B-' . Str::upper(Str::random(4)),
        'name' => 'Outlet Scope B',
        'city' => 'Bandung',
        'status' => Outlet::STATUS_ACTIVE,
    ]);
    $outletC = Outlet::query()->create([
        'tenant_id' => $tenantB->id,
        'location_id' => $locationC->id,
        'code' => 'SCOPE-C-' . Str::upper(Str::random(4)),
        'name' => 'Outlet Scope C',
        'city' => 'Surabaya',
        'status' => Outlet::STATUS_ACTIVE,
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'location_id' => $locationA->id,
    ]);

    $this->actingAs($user);

    $scopedCodes = Outlet::query()
        ->whereIn('id', [$outletA->id, $outletB->id, $outletC->id])
        ->pluck('code')
        ->all();
    $unscopedCodes = Outlet::query()
        ->withoutTenantLocation()
        ->whereIn('id', [$outletA->id, $outletB->id, $outletC->id])
        ->pluck('code')
        ->all();

    expect($scopedCodes)->toBe([$outletA->code])
        ->and($unscopedCodes)->toHaveCount(3);
});
