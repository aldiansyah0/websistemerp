<?php

namespace App\Services;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AnalyticsCacheService
{
    private const VERSION_KEY = 'erp:analytics:version';

    public function rememberDashboard(Closure $resolver): array
    {
        return $this->remember('dashboard', config('erp.cache.dashboard_ttl', 120), $resolver);
    }

    public function rememberFinancialReport(Closure $resolver): array
    {
        return $this->remember('financial-report', config('erp.cache.financial_ttl', 300), $resolver);
    }

    public function rememberCashflow(Closure $resolver): array
    {
        return $this->remember('cashflow', config('erp.cache.cashflow_ttl', 300), $resolver);
    }

    public function rememberReceivablesPayables(Closure $resolver): array
    {
        return $this->remember('receivables-payables', config('erp.cache.receivables_ttl', 300), $resolver);
    }

    public function rememberSplitPayment(Closure $resolver): array
    {
        return $this->remember('split-payment', config('erp.cache.split_payment_ttl', 180), $resolver);
    }

    public function rememberOperations(string $pageKey, Closure $resolver): array
    {
        return $this->remember('operations:' . $pageKey, config('erp.cache.operations_ttl', 120), $resolver);
    }

    public function invalidate(): void
    {
        $store = $this->store();
        $current = (int) $store->get(self::VERSION_KEY, 1);

        $store->forever(self::VERSION_KEY, $current + 1);
    }

    private function remember(string $bucket, int $seconds, Closure $resolver): array
    {
        $cacheKey = $this->cacheKey($bucket);

        /** @var array */
        return $this->store()->remember($cacheKey, now()->addSeconds($seconds), $resolver);
    }

    private function cacheKey(string $bucket): string
    {
        $user = Auth::user();
        $tenant = $user?->tenant_id ?? 'all';
        $location = $user?->location_id ?? 'all';
        $version = (int) $this->store()->get(self::VERSION_KEY, 1);

        return 'erp:analytics:v' . $version . ':' . $bucket . ':tenant:' . $tenant . ':location:' . $location;
    }

    private function store(): Repository
    {
        $configuredStore = (string) config('erp.cache.store', 'file');
        $stores = array_keys((array) config('cache.stores', []));
        $store = in_array($configuredStore, $stores, true)
            ? $configuredStore
            : (string) config('cache.default', 'file');

        return Cache::store($store);
    }
}
