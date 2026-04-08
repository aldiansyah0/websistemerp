<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodLockService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function assertDateIsOpen(CarbonInterface|string|null $date, string $context): void
    {
        $dateValue = $date !== null ? CarbonImmutable::parse($date)->toDateString() : CarbonImmutable::now('Asia/Jakarta')->toDateString();
        $user = Auth::user();

        $query = AccountingPeriod::query()
            ->withoutTenantLocation()
            ->where('status', AccountingPeriod::STATUS_CLOSED)
            ->whereDate('start_date', '<=', $dateValue)
            ->whereDate('end_date', '>=', $dateValue);

        if ($user?->tenant_id !== null) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('tenant_id', (int) $user->tenant_id)
                    ->orWhereNull('tenant_id');
            });
        }

        if ($user?->location_id !== null) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('location_id', (int) $user->location_id)
                    ->orWhereNull('location_id');
            });
        }

        $lockedPeriod = $query->orderByDesc('end_date')->first();

        if ($lockedPeriod === null) {
            return;
        }

        throw new DomainException(sprintf(
            'Periode %s (%s s/d %s) sudah ditutup. %s tidak bisa diposting.',
            $lockedPeriod->period_code,
            $lockedPeriod->start_date?->format('Y-m-d'),
            $lockedPeriod->end_date?->format('Y-m-d'),
            $context,
        ));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function closePeriod(array $attributes): AccountingPeriod
    {
        return DB::transaction(function () use ($attributes): AccountingPeriod {
            $startDate = CarbonImmutable::parse((string) $attributes['start_date'])->toDateString();
            $endDate = CarbonImmutable::parse((string) $attributes['end_date'])->toDateString();

            if ($endDate < $startDate) {
                throw new DomainException('Tanggal akhir periode tidak boleh sebelum tanggal mulai.');
            }

            $tenantId = $attributes['tenant_id'] ?? Auth::user()?->tenant_id;
            $locationId = $attributes['location_id'] ?? Auth::user()?->location_id;

            $overlap = AccountingPeriod::query()
                ->withoutTenantLocation()
                ->where('status', AccountingPeriod::STATUS_CLOSED)
                ->where(function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nested) use ($startDate, $endDate): void {
                            $nested->whereDate('start_date', '<=', $startDate)
                                ->whereDate('end_date', '>=', $endDate);
                        });
                });

            if ($tenantId !== null) {
                $overlap->where('tenant_id', (int) $tenantId);
            } else {
                $overlap->whereNull('tenant_id');
            }

            if ($locationId !== null) {
                $overlap->where('location_id', (int) $locationId);
            } else {
                $overlap->whereNull('location_id');
            }

            if ($overlap->exists()) {
                throw new DomainException('Periode ini overlap dengan periode closed yang sudah ada.');
            }

            $periodCode = (string) ($attributes['period_code'] ?? CarbonImmutable::parse($startDate)->format('Ym'));

            $period = AccountingPeriod::query()->withoutTenantLocation()->create([
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
                'period_code' => $periodCode,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => AccountingPeriod::STATUS_CLOSED,
                'closed_at' => now(),
                'closed_by' => Auth::id(),
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->auditLogService->log('finance', 'period.close', 'Periode keuangan ditutup', $period, [
                'period_code' => $period->period_code,
                'start_date' => $period->start_date?->toDateString(),
                'end_date' => $period->end_date?->toDateString(),
                'notes' => $attributes['notes'] ?? null,
                'tenant_id' => $tenantId,
                'location_id' => $locationId,
            ]);

            return $period;
        });
    }

    public function reopenPeriod(AccountingPeriod $period, ?string $notes = null): AccountingPeriod
    {
        if ($period->status !== AccountingPeriod::STATUS_CLOSED) {
            throw new DomainException('Hanya periode closed yang bisa dibuka kembali.');
        }

        $period->status = AccountingPeriod::STATUS_OPEN;
        $period->notes = trim((string) $period->notes . PHP_EOL . '[Reopen] ' . trim((string) $notes));
        $period->save();

        $this->auditLogService->log('finance', 'period.reopen', 'Periode keuangan dibuka kembali', $period, [
            'period_code' => $period->period_code,
            'notes' => $notes,
            'tenant_id' => $period->tenant_id,
            'location_id' => $period->location_id,
        ]);

        return $period;
    }
}
