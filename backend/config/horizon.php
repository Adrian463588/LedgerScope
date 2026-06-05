<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Laravel Horizon Configuration — LedgerScope
|--------------------------------------------------------------------------
| Horizon manages and monitors queue workers.
| Workers run inside Docker (Linux) — pcntl extension required.
|
| Queue segmentation:
|   default  — general low-priority tasks
|   imports  — COA and journal import jobs (high concurrency, resource-heavy)
|   reports  — PDF/XLSX report generation (medium priority)
|   notifications — email/push notifications (fast, low resource)
|
*/

return [

    'domain' => env('HORIZON_DOMAIN'),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => 'default',

    'prefix' => env(
        'HORIZON_PREFIX',
        sprintf(
            '%s-%s-horizon:',
            env('APP_NAME', 'laravel'),
            env('APP_ENV', 'production'),
        ),
    ),

    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    'silenced' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => false,

    'memory_limit' => 256,

    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 5,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 60,
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-imports' => [
                'connection' => 'redis',
                'queue' => ['imports'],
                'balance' => 'auto',
                'maxProcesses' => 4,
                'tries' => 2,
                'timeout' => 300,
            ],
            'supervisor-reports' => [
                'connection' => 'redis',
                'queue' => ['reports'],
                'balance' => 'auto',
                'maxProcesses' => 3,
                'tries' => 2,
                'timeout' => 600,
            ],
            'supervisor-notifications' => [
                'connection' => 'redis',
                'queue' => ['notifications'],
                'balance' => 'auto',
                'maxProcesses' => 5,
                'tries' => 3,
                'timeout' => 30,
            ],
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'maxProcesses' => 3,
                'tries' => 3,
                'timeout' => 60,
            ],
        ],

        'local' => [
            'supervisor-local' => [
                'connection' => 'redis',
                'queue' => ['default', 'imports', 'reports', 'notifications'],
                'balance' => 'simple',
                'maxProcesses' => 3,
                'tries' => 3,
                'timeout' => 120,
            ],
        ],
    ],

];
