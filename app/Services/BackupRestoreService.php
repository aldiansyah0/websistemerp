<?php

namespace App\Services;

use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class BackupRestoreService
{
    /**
     * @param array<int, string>|null $tables
     * @return array{file_path: string, file_name: string, table_count: int, row_count: int}
     */
    public function backup(?string $fileName = null, ?array $tables = null, string $disk = 'local'): array
    {
        $tables = $tables ?? $this->backupTables();
        $fileName = trim((string) ($fileName ?: 'backup-' . now('Asia/Jakarta')->format('Ymd-His') . '.json'));
        $filePath = str_starts_with($fileName, 'backups/') ? $fileName : 'backups/' . $fileName;
        $rowCount = 0;
        $tablePayload = [];

        $payload = [
            'meta' => [
                'generated_at' => now('Asia/Jakarta')->toIso8601String(),
                'environment' => app()->environment(),
                'connection' => config('database.default'),
            ],
            'tables' => [],
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get()->map(fn ($row): array => (array) $row)->all();
            $rowCount += count($rows);
            $tablePayload[$table] = $rows;
        }

        $checksum = hash(
            'sha256',
            json_encode($tablePayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        $payload['meta']['checksum_sha256'] = $checksum;
        $payload['meta']['table_count'] = count($tablePayload);
        $payload['meta']['row_count'] = $rowCount;
        $payload['tables'] = $tablePayload;

        Storage::disk($disk)->put(
            $filePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        return [
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'table_count' => count($payload['tables']),
            'row_count' => $rowCount,
        ];
    }

    /**
     * @return array{table_count: int, row_count: int}
     */
    public function restore(string $filePath, string $disk = 'local'): array
    {
        $payload = $this->loadBackupPayload($filePath, $disk);
        $this->assertPayloadIntegrity($payload);

        $tables = array_keys($payload['tables']);
        $rowCount = 0;
        $restoredTables = 0;
        $restoreRunner = function () use ($payload, $tables, &$rowCount, &$restoredTables): void {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $tableReplaced = false;

                try {
                    DB::table($table)->delete();
                    $tableReplaced = true;
                } catch (QueryException) {
                    $tableReplaced = false;
                }

                $restoredTables++;

                $rows = $payload['tables'][$table] ?? [];
                if (! is_array($rows) || $rows === []) {
                    continue;
                }

                $canUpsertById = Schema::hasColumn($table, 'id');

                foreach (array_chunk($rows, 500) as $chunk) {
                    if ($tableReplaced) {
                        DB::table($table)->insert($chunk);
                    } elseif ($canUpsertById) {
                        DB::table($table)->upsert($chunk, ['id']);
                    } else {
                        DB::table($table)->insertOrIgnore($chunk);
                    }

                    $rowCount += count($chunk);
                }
            }
        };

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            try {
                $restoreRunner();
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        } else {
            Schema::disableForeignKeyConstraints();

            try {
                DB::transaction($restoreRunner);
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        }

        return [
            'table_count' => $restoredTables,
            'row_count' => $rowCount,
        ];
    }

    /**
     * @return array{generated_at: string|null, table_count: int, row_count: int, checksum_valid: bool, file_path: string}
     */
    public function inspect(string $filePath, string $disk = 'local'): array
    {
        $payload = $this->loadBackupPayload($filePath, $disk);
        $this->assertPayloadIntegrity($payload);

        return [
            'generated_at' => $payload['meta']['generated_at'] ?? null,
            'table_count' => count($payload['tables']),
            'row_count' => collect($payload['tables'])->sum(fn (mixed $rows): int => is_array($rows) ? count($rows) : 0),
            'checksum_valid' => true,
            'file_path' => $filePath,
        ];
    }

    /**
     * @return array{deleted_count: int, kept_count: int, deleted_files: array<int, string>}
     */
    public function pruneOldBackups(int $keepDays = 14, string $disk = 'local'): array
    {
        $keepDays = max($keepDays, 1);
        $cutoff = now('Asia/Jakarta')->subDays($keepDays)->getTimestamp();
        $deletedFiles = [];

        foreach (Storage::disk($disk)->files('backups') as $path) {
            $lastModified = Storage::disk($disk)->lastModified($path);
            if ($lastModified >= $cutoff) {
                continue;
            }

            if (Storage::disk($disk)->delete($path)) {
                $deletedFiles[] = $path;
            }
        }

        return [
            'deleted_count' => count($deletedFiles),
            'kept_count' => count(Storage::disk($disk)->files('backups')),
            'deleted_files' => $deletedFiles,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function backupTables(): array
    {
        $excluded = [
            'migrations',
            'jobs',
            'job_batches',
            'failed_jobs',
            'sessions',
            'cache',
            'cache_locks',
            'password_reset_tokens',
            'personal_access_tokens',
        ];

        return collect(Schema::getTableListing())
            ->reject(fn (string $table): bool => in_array($table, $excluded, true))
            ->values()
            ->all();
    }

    /**
     * @return array{meta: array<string, mixed>, tables: array<string, array<int, array<string, mixed>>>}
     */
    private function loadBackupPayload(string $filePath, string $disk): array
    {
        if (! Storage::disk($disk)->exists($filePath)) {
            throw new DomainException('File backup tidak ditemukan: ' . $filePath);
        }

        $rawPayload = Storage::disk($disk)->get($filePath);
        $payload = json_decode($rawPayload, true);

        if (! is_array($payload) || ! is_array($payload['tables'] ?? null)) {
            throw new DomainException('Format file backup tidak valid.');
        }

        return $payload;
    }

    /**
     * @param array{meta?: array<string, mixed>, tables: array<string, mixed>} $payload
     */
    private function assertPayloadIntegrity(array $payload): void
    {
        $expectedChecksum = (string) ($payload['meta']['checksum_sha256'] ?? '');
        if ($expectedChecksum === '') {
            return;
        }

        $computedChecksum = hash(
            'sha256',
            json_encode($payload['tables'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        if (! hash_equals($expectedChecksum, $computedChecksum)) {
            throw new DomainException('Checksum backup tidak valid. File kemungkinan korup atau berubah.');
        }
    }
}
