<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function log(
        string $module,
        string $action,
        string $event,
        Model|string|null $auditable = null,
        array $metadata = [],
    ): AuditLog {
        $user = Auth::user();
        $request = request();

        return AuditLog::query()->withoutTenantLocation()->create([
            'tenant_id' => $metadata['tenant_id'] ?? $user?->tenant_id,
            'location_id' => $metadata['location_id'] ?? $user?->location_id,
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'event' => $event,
            'auditable_type' => $auditable instanceof Model ? $auditable->getMorphClass() : (is_string($auditable) ? $auditable : null),
            'auditable_id' => $auditable instanceof Model ? $auditable->getKey() : null,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}

