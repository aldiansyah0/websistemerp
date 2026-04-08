<?php

use App\Services\AnalyticsCacheService;

test('analytics cache remembers and rotates payload after invalidation', function () {
    $cache = app(AnalyticsCacheService::class);

    $first = $cache->rememberDashboard(fn (): array => ['token' => 'first']);
    $second = $cache->rememberDashboard(fn (): array => ['token' => 'second']);

    expect($first['token'])->toBe('first')
        ->and($second['token'])->toBe('first');

    $cache->invalidate();
    $third = $cache->rememberDashboard(fn (): array => ['token' => 'third']);

    expect($third['token'])->toBe('third');
});
