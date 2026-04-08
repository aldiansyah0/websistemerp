<?php

use App\Models\AuditLog;
use App\Models\User;

beforeEach(function (): void {
    $this->seed();
});

test('finance can access audit trail workspace and see live records', function () {
    $finance = User::query()->where('email', 'finance@webstellar.local')->firstOrFail();

    $this->actingAs($finance);

    AuditLog::query()->withoutTenantLocation()->create([
        'tenant_id' => $finance->tenant_id,
        'location_id' => $finance->location_id,
        'user_id' => $finance->id,
        'module' => 'qa_audit_finance',
        'action' => 'qa_audit_finance.approve',
        'event' => 'Finance approval test record',
        'metadata' => ['reference' => 'QA-AUDIT-001'],
    ]);

    $this->get(route('audit-trail'))
        ->assertOk()
        ->assertSee('Audit Trail')
        ->assertSee('Finance approval test record');
});

test('cashier cannot access audit trail workspace', function () {
    $cashier = User::query()->where('email', 'cashier@webstellar.local')->firstOrFail();

    $this->actingAs($cashier)
        ->get(route('audit-trail'))
        ->assertForbidden();
});

