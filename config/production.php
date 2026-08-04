<?php

return [
    'version' => env('APP_VERSION', 'dev'),
    'commit_sha' => env('APP_COMMIT_SHA'),
    'build_number' => env('APP_BUILD_NUMBER'),

    'force_https' => env('FORCE_HTTPS', false),

    'security' => [
        'csp_mode' => env('CSP_MODE', 'report-only'),
        'hsts_max_age' => (int) env('HSTS_MAX_AGE', 31536000),
        'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'hsts_preload' => env('HSTS_PRELOAD', false),
    ],

    'health' => [
        'expose_details' => env('HEALTH_EXPOSE_DETAILS', false),
    ],

    'backup' => [
        'path' => env('BACKUP_PATH') ?: storage_path('app/backups'),
        'keep' => (int) env('BACKUP_KEEP', 14),
        'time' => env('BACKUP_TIME', '02:15'),
        'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'pg_dump_binary' => env('PG_DUMP_BINARY', 'pg_dump'),
    ],

    'monitoring' => [
        'slow_query_ms' => (int) env('SLOW_QUERY_MS', 750),
        'activity_retention_days' => (int) env('ACTIVITY_RETENTION_DAYS', 180),
    ],
];
