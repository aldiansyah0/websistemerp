<?php

return [
    'cache' => [
        /*
         * Use Redis when available, otherwise fallback to file cache.
         * This cache bucket is dedicated for heavy dashboard/reporting reads.
         */
        'store' => env('ERP_CACHE_STORE', env('CACHE_STORE') === 'database' ? 'file' : env('CACHE_STORE', 'file')),
        'operations_ttl' => (int) env('ERP_CACHE_OPERATIONS_TTL', 120),
        'dashboard_ttl' => (int) env('ERP_CACHE_DASHBOARD_TTL', 120),
        'financial_ttl' => (int) env('ERP_CACHE_FINANCIAL_TTL', 300),
        'cashflow_ttl' => (int) env('ERP_CACHE_CASHFLOW_TTL', 300),
        'receivables_ttl' => (int) env('ERP_CACHE_RECEIVABLES_TTL', 300),
        'split_payment_ttl' => (int) env('ERP_CACHE_SPLIT_PAYMENT_TTL', 180),
    ],
    'stock' => [
        'enforce_minimum' => (bool) env('ERP_STOCK_ENFORCE_MINIMUM', false),
    ],
    'export' => [
        'chunk_size' => (int) env('ERP_EXPORT_CHUNK_SIZE', 1000),
        'queue' => env('ERP_EXPORT_QUEUE', 'reports'),
        'tries' => (int) env('ERP_EXPORT_TRIES', 3),
        'timeout' => (int) env('ERP_EXPORT_TIMEOUT', 300),
        'backoff_seconds' => (int) env('ERP_EXPORT_BACKOFF_SECONDS', 10),
        'retry_window_minutes' => (int) env('ERP_EXPORT_RETRY_WINDOW_MINUTES', 30),
    ],
    'queue' => [
        'reports' => [
            'queue' => env('ERP_REPORT_WORKER_QUEUE', env('ERP_EXPORT_QUEUE', 'reports')),
            'sleep' => (int) env('ERP_REPORT_WORKER_SLEEP', 1),
            'tries' => (int) env('ERP_REPORT_WORKER_TRIES', env('ERP_EXPORT_TRIES', 3)),
            'backoff' => (int) env('ERP_REPORT_WORKER_BACKOFF', env('ERP_EXPORT_BACKOFF_SECONDS', 10)),
            'timeout' => (int) env('ERP_REPORT_WORKER_TIMEOUT', env('ERP_EXPORT_TIMEOUT', 300)),
            'memory' => (int) env('ERP_REPORT_WORKER_MEMORY', 512),
            'max_jobs' => (int) env('ERP_REPORT_WORKER_MAX_JOBS', 100),
            'max_time' => (int) env('ERP_REPORT_WORKER_MAX_TIME', 3600),
        ],
        'monitor' => [
            'pending_threshold' => (int) env('ERP_QUEUE_PENDING_THRESHOLD', 200),
            'failed_threshold' => (int) env('ERP_QUEUE_FAILED_THRESHOLD', 1),
            'failed_window_minutes' => (int) env('ERP_QUEUE_FAILED_WINDOW_MINUTES', 60),
        ],
    ],
    'backup' => [
        'retention_days' => (int) env('ERP_BACKUP_RETENTION_DAYS', 14),
    ],
];
