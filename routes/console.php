<?php

use App\Services\BackupRestoreService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('erp:backup {--file=} {--disk=local} {--tables=}', function (BackupRestoreService $backupRestoreService): int {
    $tablesOption = trim((string) $this->option('tables'));
    $tables = $tablesOption !== ''
        ? collect(explode(',', $tablesOption))->map(fn (string $table): string => trim($table))->filter()->values()->all()
        : null;

    $result = $backupRestoreService->backup(
        fileName: $this->option('file') ?: null,
        tables: $tables,
        disk: (string) $this->option('disk'),
    );

    $this->info('Backup selesai: ' . $result['file_path']);
    $this->line('Tables: ' . $result['table_count'] . ' | Rows: ' . $result['row_count']);

    return 0;
})->purpose('Backup data ERP ke file JSON di storage/app/backups');

Artisan::command('erp:restore {file} {--disk=local} {--force}', function (BackupRestoreService $backupRestoreService): int {
    if (app()->environment('production') && ! (bool) $this->option('force')) {
        $this->error('Restore diblokir di production. Tambahkan --force jika sudah validasi backup.');

        return 1;
    }

    try {
        $result = $backupRestoreService->restore(
            filePath: (string) $this->argument('file'),
            disk: (string) $this->option('disk'),
        );
    } catch (\DomainException $exception) {
        Log::channel('alerts')->critical('ERP restore failed', [
            'file' => (string) $this->argument('file'),
            'message' => $exception->getMessage(),
        ]);

        $this->error($exception->getMessage());

        return 1;
    }

    $this->info('Restore selesai.');
    $this->line('Tables: ' . $result['table_count'] . ' | Rows: ' . $result['row_count']);

    return 0;
})->purpose('Restore data ERP dari file backup JSON');

Artisan::command('erp:backup:verify {file} {--disk=local}', function (BackupRestoreService $backupRestoreService): int {
    try {
        $result = $backupRestoreService->inspect(
            filePath: (string) $this->argument('file'),
            disk: (string) $this->option('disk'),
        );
    } catch (\DomainException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    $this->info('File backup valid: ' . $result['file_path']);
    $this->line('Generated at: ' . ($result['generated_at'] ?? '-'));
    $this->line('Tables: ' . $result['table_count'] . ' | Rows: ' . $result['row_count']);

    return 0;
})->purpose('Validasi struktur dan checksum file backup ERP');

Artisan::command('erp:backup:prune {--disk=local} {--keep-days=14}', function (BackupRestoreService $backupRestoreService): int {
    $result = $backupRestoreService->pruneOldBackups(
        keepDays: (int) $this->option('keep-days'),
        disk: (string) $this->option('disk'),
    );

    $this->info('Pruning backup selesai.');
    $this->line('Deleted: ' . $result['deleted_count'] . ' | Kept: ' . $result['kept_count']);

    return 0;
})->purpose('Hapus file backup lama berdasarkan retensi hari');

Artisan::command('erp:work-reports {--once} {--stop-when-empty}', function (): int {
    $arguments = [
        '--queue' => (string) config('erp.queue.reports.queue', config('erp.export.queue', 'reports')),
        '--sleep' => (int) config('erp.queue.reports.sleep', 1),
        '--tries' => (int) config('erp.queue.reports.tries', config('erp.export.tries', 3)),
        '--backoff' => (int) config('erp.queue.reports.backoff', config('erp.export.backoff_seconds', 10)),
        '--timeout' => (int) config('erp.queue.reports.timeout', config('erp.export.timeout', 300)),
        '--memory' => (int) config('erp.queue.reports.memory', 512),
    ];

    if ((bool) $this->option('once')) {
        $arguments['--once'] = true;
    } else {
        $arguments['--max-jobs'] = (int) config('erp.queue.reports.max_jobs', 100);
        $arguments['--max-time'] = (int) config('erp.queue.reports.max_time', 3600);
    }

    if ((bool) $this->option('stop-when-empty')) {
        $arguments['--stop-when-empty'] = true;
    }

    return Artisan::call('queue:work', $arguments);
})->purpose('Jalankan worker queue reports dengan retry policy ERP yang sudah ditetapkan');

Artisan::command('erp:queue:monitor {--pending-threshold=} {--failed-threshold=}', function (): int {
    if (! Schema::hasTable('jobs') || ! Schema::hasTable('failed_jobs')) {
        $this->error('Tabel jobs/failed_jobs belum tersedia untuk monitoring queue.');

        return 1;
    }

    $queue = (string) config('erp.queue.reports.queue', config('erp.export.queue', 'reports'));
    $pendingThreshold = (int) ($this->option('pending-threshold') ?: config('erp.queue.monitor.pending_threshold', 200));
    $failedThreshold = (int) ($this->option('failed-threshold') ?: config('erp.queue.monitor.failed_threshold', 1));
    $failedWindowMinutes = (int) config('erp.queue.monitor.failed_window_minutes', 60);

    $pendingCount = DB::table('jobs')->where('queue', $queue)->count();
    $failedCount = DB::table('failed_jobs')
        ->where('queue', $queue)
        ->where('failed_at', '>=', now('Asia/Jakarta')->subMinutes($failedWindowMinutes))
        ->count();

    $this->line('Queue: ' . $queue . ' | Pending: ' . $pendingCount . ' | Failed window: ' . $failedCount);

    if ($pendingCount > $pendingThreshold || $failedCount > $failedThreshold) {
        Log::channel('alerts')->critical('ERP queue monitor threshold exceeded', [
            'queue' => $queue,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
            'pending_threshold' => $pendingThreshold,
            'failed_threshold' => $failedThreshold,
            'failed_window_minutes' => $failedWindowMinutes,
        ]);

        $this->error('Queue monitor threshold exceeded.');

        return 1;
    }

    $this->info('Queue monitor normal.');

    return 0;
})->purpose('Monitoring antrean report dan trigger alert jika melebihi threshold');
