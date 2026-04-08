<?php

use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->seed();
});

test('report worker command can run one-shot without error', function () {
    $this->artisan('erp:work-reports', [
        '--once' => true,
        '--stop-when-empty' => true,
    ])->assertExitCode(0);
});

test('queue monitor returns failure when pending queue exceeds threshold', function () {
    DB::table('jobs')->insert([
        'queue' => (string) config('erp.queue.reports.queue', 'reports'),
        'payload' => json_encode(['displayName' => 'Queue Monitor Test Job']),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $this->artisan('erp:queue:monitor', [
        '--pending-threshold' => -1,
    ])
        ->expectsOutputToContain('Queue monitor threshold exceeded.')
        ->assertExitCode(1);
});

test('queue monitor returns success when queue is under threshold', function () {
    $this->artisan('erp:queue:monitor', [
        '--pending-threshold' => 9999,
        '--failed-threshold' => 9999,
    ])
        ->expectsOutputToContain('Queue monitor normal.')
        ->assertExitCode(0);
});
