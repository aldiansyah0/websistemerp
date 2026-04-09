<?php

use App\Models\Location;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed();
});

test('manager with assigned location scope only sees assigned outlet data when active location is unset', function () {
    $manager = User::query()->where('email', 'manager@webstellar.local')->firstOrFail();
    $manager->forceFill([
        'location_id' => null,
        'active_location_id' => null,
    ])->save();

    $detachedLocation = createDetachedOutletForLocationAccess($manager->tenant_id);

    $assignedIds = $manager->allowedLocationIds();
    expect($assignedIds)->not->toContain((int) $detachedLocation['location']->id);

    $this->actingAs($manager);
    $visibleLocationIds = Outlet::query()
        ->pluck('location_id')
        ->filter()
        ->map(fn ($id): int => (int) $id)
        ->unique()
        ->values()
        ->all();

    foreach ($visibleLocationIds as $locationId) {
        expect($assignedIds)->toContain($locationId);
    }

    expect($visibleLocationIds)->not->toContain((int) $detachedLocation['location']->id);
});

test('manager can switch active location and query scope follows the chosen outlet', function () {
    $manager = User::query()->where('email', 'manager@webstellar.local')->firstOrFail();

    $assignedOutletLocationIds = Outlet::query()
        ->withoutTenantLocation()
        ->whereIn('location_id', $manager->allowedLocationIds())
        ->pluck('location_id')
        ->filter()
        ->map(fn ($id): int => (int) $id)
        ->unique()
        ->values();

    $targetLocationId = (int) $assignedOutletLocationIds->first();
    expect($targetLocationId)->toBeGreaterThan(0);

    $this->actingAs($manager)
        ->from(route('dashboard'))
        ->post(route('active-location.switch'), [
            'location_id' => $targetLocationId,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success');

    $manager->refresh();
    expect((int) $manager->active_location_id)->toBe($targetLocationId)
        ->and((int) $manager->location_id)->toBe($targetLocationId);

    $this->actingAs($manager);
    $scopedOutletLocationIds = Outlet::query()
        ->pluck('location_id')
        ->filter()
        ->map(fn ($id): int => (int) $id)
        ->unique()
        ->values()
        ->all();

    expect($scopedOutletLocationIds)->toEqual([$targetLocationId]);
});

test('manager cannot switch active location to an unassigned outlet', function () {
    $manager = User::query()->where('email', 'manager@webstellar.local')->firstOrFail();
    $detachedLocation = createDetachedOutletForLocationAccess($manager->tenant_id);

    $this->actingAs($manager)
        ->from(route('dashboard'))
        ->post(route('active-location.switch'), [
            'location_id' => (int) $detachedLocation['location']->id,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');

    $manager->refresh();
    expect($manager->canAccessLocation((int) $detachedLocation['location']->id))->toBeFalse();
});

test('single location user cannot clear active location context', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();

    $this->actingAs($cashier)
        ->from(route('dashboard'))
        ->post(route('active-location.switch'), [
            'location_id' => '',
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');
});

/**
 * @return array{location: Location, outlet: Outlet}
 */
function createDetachedOutletForLocationAccess(?int $tenantId): array
{
    $suffix = strtoupper(Str::random(6));
    $location = Location::query()->withoutTenantLocation()->create([
        'tenant_id' => $tenantId,
        'type' => Location::TYPE_OUTLET,
        'code' => 'DET-' . $suffix,
        'name' => 'Detached Outlet ' . $suffix,
        'city' => 'Detached City',
        'status' => 'active',
    ]);

    $outlet = Outlet::query()->withoutTenantLocation()->create([
        'tenant_id' => $tenantId,
        'location_id' => $location->id,
        'code' => 'OUT-' . $suffix,
        'name' => 'Detached Outlet ' . $suffix,
        'city' => 'Detached City',
        'status' => Outlet::STATUS_ACTIVE,
    ]);

    $location->forceFill([
        'legacy_outlet_id' => $outlet->id,
    ])->saveQuietly();

    return [
        'location' => $location->fresh(),
        'outlet' => $outlet->fresh(),
    ];
}
