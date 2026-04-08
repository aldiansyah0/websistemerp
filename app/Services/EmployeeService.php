<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Location;
use App\Models\Outlet;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(
        private readonly AnalyticsCacheService $analyticsCacheService,
    ) {
    }

    public function create(array $attributes): Employee
    {
        return DB::transaction(function () use ($attributes): Employee {
            $payload = $this->normalizeLocationPayload($attributes);
            $employee = Employee::query()->create($payload);
            $this->analyticsCacheService->invalidate();

            return $employee->fresh(['outlet', 'location']);
        });
    }

    public function update(Employee $employee, array $attributes): Employee
    {
        return DB::transaction(function () use ($employee, $attributes): Employee {
            $payload = $this->normalizeLocationPayload($attributes);
            $employee->update($payload);
            $this->analyticsCacheService->invalidate();

            return $employee->fresh(['outlet', 'location']);
        });
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function normalizeLocationPayload(array $attributes): array
    {
        $locationId = Arr::get($attributes, 'location_id');
        $outletId = Arr::get($attributes, 'outlet_id');

        if (filled($locationId)) {
            $location = Location::query()->find((int) $locationId);

            if (! $location) {
                throw new DomainException('Lokasi kerja tidak ditemukan.');
            }

            $attributes['location_id'] = $location->id;
            $attributes['outlet_id'] = $location->type === Location::TYPE_OUTLET ? $location->legacy_outlet_id : null;

            return $attributes;
        }

        if (filled($outletId)) {
            $outlet = Outlet::query()->find((int) $outletId);

            if (! $outlet) {
                throw new DomainException('Outlet tidak ditemukan.');
            }

            $attributes['outlet_id'] = $outlet->id;
            $attributes['location_id'] = $outlet->location_id;

            return $attributes;
        }

        $attributes['outlet_id'] = null;
        $attributes['location_id'] = null;

        return $attributes;
    }
}
