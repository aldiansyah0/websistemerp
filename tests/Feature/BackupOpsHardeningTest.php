<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed();
});

test('backup verify command validates generated backup file', function () {
    $backupFile = 'backups/hardening-verify.json';

    $this->artisan('erp:backup', [
        '--file' => $backupFile,
        '--disk' => 'local',
    ])->assertExitCode(0);

    $this->artisan('erp:backup:verify', [
        'file' => $backupFile,
        '--disk' => 'local',
    ])
        ->expectsOutputToContain('File backup valid:')
        ->assertExitCode(0);
});

test('restore resets data to backup snapshot and removes post-backup records for restored tables', function () {
    $backupFile = 'backups/hardening-snapshot.json';
    $owner = User::query()->where('email', 'owner@webstellar.local')->firstOrFail();

    $this->artisan('erp:backup', [
        '--file' => $backupFile,
        '--disk' => 'local',
        '--tables' => 'audit_logs',
    ])->assertExitCode(0);

    AuditLog::query()->withoutTenantLocation()->create([
        'tenant_id' => $owner->tenant_id,
        'location_id' => $owner->location_id,
        'user_id' => $owner->id,
        'module' => 'qa_backup_restore',
        'action' => 'qa_backup_restore.post_backup_insert',
        'event' => 'This row must be removed by restore snapshot',
        'metadata' => ['reference' => 'HARDENING-SNAPSHOT'],
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'This row must be removed by restore snapshot',
    ]);

    $this->artisan('erp:restore', [
        'file' => $backupFile,
        '--disk' => 'local',
        '--force' => true,
    ])->assertExitCode(0);

    $this->assertDatabaseMissing('audit_logs', [
        'event' => 'This row must be removed by restore snapshot',
    ]);
});

test('backup prune command removes expired files based on retention', function () {
    $oldBackup = 'backups/hardening-old.json';
    $newBackup = 'backups/hardening-new.json';

    Storage::disk('local')->put($oldBackup, json_encode(['meta' => [], 'tables' => []]));
    Storage::disk('local')->put($newBackup, json_encode(['meta' => [], 'tables' => []]));

    touch(Storage::disk('local')->path($oldBackup), now()->subDays(40)->timestamp);
    touch(Storage::disk('local')->path($newBackup), now()->subDays(2)->timestamp);

    $this->artisan('erp:backup:prune', [
        '--disk' => 'local',
        '--keep-days' => 14,
    ])->assertExitCode(0);

    expect(Storage::disk('local')->exists($oldBackup))->toBeFalse()
        ->and(Storage::disk('local')->exists($newBackup))->toBeTrue();
});
