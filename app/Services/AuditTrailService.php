<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class AuditTrailService
{
    /**
     * @param array<string, mixed> $filters
     * @return array{
     *   logs: LengthAwarePaginator<int, AuditLog>,
     *   moduleOptions: array<int, string>,
     *   actionOptions: array<int, string>,
     *   userOptions: array<int, array{id:int, name:string}>,
     *   summary: array{total:int, today:int, approvals:int, failures:int}
     * }
     */
    public function panelData(array $filters, int $perPage = 25): array
    {
        $query = AuditLog::query()
            ->with('user')
            ->orderByDesc('id');

        $this->applyFilters($query, $filters);

        $safePerPage = min(max($perPage, 10), 100);
        $summarySource = clone $query;
        $logs = $query->paginate($safePerPage);

        return [
            'logs' => $logs,
            'moduleOptions' => $this->moduleOptions(),
            'actionOptions' => $this->actionOptions(),
            'userOptions' => $this->userOptions(),
            'summary' => [
                'total' => (int) (clone $summarySource)->count(),
                'today' => (int) (clone $summarySource)->whereDate('created_at', now('Asia/Jakarta')->toDateString())->count(),
                'approvals' => (int) (clone $summarySource)
                    ->where(function (Builder $builder): void {
                        $builder
                            ->where('action', 'like', '%.approve')
                            ->orWhere('action', 'like', '%.reject')
                            ->orWhere('action', 'like', '%.submit')
                            ->orWhere('action', 'like', '%.pay');
                    })
                    ->count(),
                'failures' => (int) (clone $summarySource)
                    ->where(function (Builder $builder): void {
                        $builder
                            ->where('action', 'like', '%.failed')
                            ->orWhere('event', 'like', '%gagal%');
                    })
                    ->count(),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $module = trim((string) ($filters['module'] ?? ''));
        if ($module !== '') {
            $query->where('module', $module);
        }

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $query->where('action', $action);
        }

        $userId = (int) ($filters['user_id'] ?? 0);
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('module', 'like', '%' . $search . '%')
                    ->orWhere('action', 'like', '%' . $search . '%')
                    ->orWhere('event', 'like', '%' . $search . '%')
                    ->orWhere('auditable_type', 'like', '%' . $search . '%')
                    ->orWhere('auditable_id', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        $dateFrom = $this->safeDate($filters['date_from'] ?? null);
        if ($dateFrom !== null) {
            $query->whereDate('created_at', '>=', $dateFrom->toDateString());
        }

        $dateTo = $this->safeDate($filters['date_to'] ?? null);
        if ($dateTo !== null) {
            $query->whereDate('created_at', '<=', $dateTo->toDateString());
        }
    }

    /**
     * @return array<int, string>
     */
    private function moduleOptions(): array
    {
        return AuditLog::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function actionOptions(): array
    {
        return AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    private function userOptions(): array
    {
        return User::query()
            ->whereIn('id', AuditLog::query()->select('user_id')->whereNotNull('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'id' => (int) $user->id,
                'name' => $user->name,
            ])
            ->all();
    }

    private function safeDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
