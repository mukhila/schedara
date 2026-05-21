<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    */
    'domain' => env('HORIZON_DOMAIN'),
    'path'   => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    */
    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Application Name
    |--------------------------------------------------------------------------
    */
    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'Schedara'), '_') . '_horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds (seconds → notify)
    |--------------------------------------------------------------------------
    */
    'waits' => [
        'redis:image-optimization' => 60,
        'redis:video-compression'  => 300,
        'redis:media'              => 60,
        'redis:media-ai'           => 120,
        'redis:publishing'         => 30,
        'redis:social'             => 60,
        'redis:analytics'          => 120,
        'redis:notifications'      => 30,
        'redis:default'            => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming (minutes)
    |--------------------------------------------------------------------------
    */
    'trim' => [
        'recent'        => 60,
        'pending'       => 60,
        'completed'     => 120,
        'recent_failed' => 10080,   // 7 days
        'failed'        => 10080,
        'monitored'     => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs (excluded from dashboard)
    |--------------------------------------------------------------------------
    */
    'silenced' => [],

    /*
    |--------------------------------------------------------------------------
    | Metrics (snapshot interval in minutes)
    |--------------------------------------------------------------------------
    */
    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    */
    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    */
    'memory_limit' => 512,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Environments & Supervisor Configs
    |--------------------------------------------------------------------------
    |
    | "local" and "production" each define named supervisors. Each supervisor
    | targets specific queues and controls concurrency / worker count.
    |
    */
    'environments' => [

        'production' => [

            // ── Default catch-all ────────────────────────────────────────────
            'supervisor-default' => [
                'connection' => 'redis',
                'queue'      => ['default'],
                'balance'    => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 10,
                'maxTime'      => 0,
                'maxJobs'      => 0,
                'memory'       => 256,
                'tries'        => 3,
                'timeout'      => 90,
                'sleep'        => 3,
                'nice'         => 0,
            ],

            // ── Social account sync & OAuth refresh ──────────────────────────
            'supervisor-social' => [
                'connection'  => 'redis',
                'queue'       => ['social'],
                'balance'     => 'simple',
                'processes'   => 3,
                'maxTime'     => 0,
                'memory'      => 256,
                'tries'       => 3,
                'timeout'     => 120,
                'sleep'       => 3,
            ],

            // ── Post publishing ──────────────────────────────────────────────
            'supervisor-publishing' => [
                'connection'  => 'redis',
                'queue'       => ['publishing'],
                'balance'     => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 8,
                'minProcesses' => 1,
                'memory'       => 256,
                'tries'        => 3,
                'timeout'      => 120,
                'sleep'        => 3,
            ],

            // ── Image optimization ───────────────────────────────────────────
            'supervisor-image' => [
                'connection'  => 'redis',
                'queue'       => ['image-optimization'],
                'balance'     => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 4,
                'minProcesses' => 1,
                'memory'       => 512,
                'tries'        => 2,
                'timeout'      => 300,
                'sleep'        => 5,
            ],

            // ── Video compression (CPU/memory intensive) ─────────────────────
            'supervisor-video' => [
                'connection'  => 'redis',
                'queue'       => ['video-compression'],
                'balance'     => 'simple',
                'processes'   => 2,
                'memory'      => 1024,
                'tries'       => 2,
                'timeout'     => 3600,
                'sleep'       => 10,
            ],

            // ── General media tasks (upload events, CDN sync) ────────────────
            'supervisor-media' => [
                'connection'  => 'redis',
                'queue'       => ['media'],
                'balance'     => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 5,
                'minProcesses' => 1,
                'memory'       => 256,
                'tries'        => 3,
                'timeout'      => 120,
                'sleep'        => 3,
            ],

            // ── AI tagging (OpenAI Vision calls) ─────────────────────────────
            'supervisor-media-ai' => [
                'connection'  => 'redis',
                'queue'       => ['media-ai'],
                'balance'     => 'simple',
                'processes'   => 3,
                'memory'      => 256,
                'tries'       => 2,
                'timeout'      => 60,
                'sleep'       => 5,
            ],

            // ── AI Marketing Assistant (caption, campaign generation) ─────────
            'supervisor-ai' => [
                'connection'  => 'redis',
                'queue'       => ['ai'],
                'balance'     => 'auto',
                'maxProcesses' => 4,
                'minProcesses' => 1,
                'memory'       => 384,
                'tries'        => 3,
                'timeout'      => 300,
                'sleep'        => 3,
            ],

            // ── Analytics sync & report generation ───────────────────────────
            'supervisor-analytics' => [
                'connection'  => 'redis',
                'queue'       => ['analytics'],
                'balance'     => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 6,
                'minProcesses' => 1,
                'memory'       => 256,
                'tries'        => 3,
                'timeout'      => 600,
                'sleep'        => 5,
            ],

            // ── Client report generation (PDF/Excel) ─────────────────────────
            'supervisor-reports' => [
                'connection'  => 'redis',
                'queue'       => ['reports'],
                'balance'     => 'simple',
                'processes'   => 3,
                'memory'      => 512,
                'tries'        => 3,
                'timeout'      => 300,
                'sleep'        => 5,
            ],

            // ── Client billing & subscription jobs ───────────────────────────
            'supervisor-billing' => [
                'connection'  => 'redis',
                'queue'       => ['billing'],
                'balance'     => 'simple',
                'processes'   => 2,
                'memory'      => 256,
                'tries'        => 3,
                'timeout'      => 120,
                'sleep'        => 3,
            ],

            // ── Transactional emails (invoices, onboarding) ──────────────────
            'supervisor-emails' => [
                'connection'  => 'redis',
                'queue'       => ['emails'],
                'balance'     => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 5,
                'minProcesses' => 1,
                'memory'       => 128,
                'tries'        => 3,
                'timeout'      => 60,
                'sleep'        => 3,
            ],

            // ── Multi-channel notification dispatch (push/WhatsApp/SMS/Slack) ─
            'supervisor-notifications' => [
                'connection'  => 'redis',
                'queue'       => ['notifications'],
                'balance'     => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 6,
                'minProcesses' => 1,
                'memory'       => 128,
                'tries'        => 3,
                'timeout'      => 60,
                'sleep'        => 3,
            ],
        ],

        'local' => [

            'supervisor-all' => [
                'connection'  => 'redis',
                'queue'       => [
                    'default', 'social', 'publishing',
                    'image-optimization', 'video-compression',
                    'media', 'media-ai', 'analytics', 'ai',
                    'reports', 'billing', 'emails', 'notifications',
                ],
                'balance'     => 'simple',
                'processes'   => 4,
                'memory'      => 512,
                'tries'       => 3,
                'timeout'     => 3600,
                'sleep'       => 3,
            ],
        ],
    ],
];
