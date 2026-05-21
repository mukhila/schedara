<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'openai' => [
            'api_key'       => env('OPENAI_API_KEY'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
            'models'        => ['gpt-4o', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'base_url'      => 'https://api.openai.com/v1',
            'timeout'       => 90,
            // Cost per 1 000 tokens in USD
            'pricing' => [
                'gpt-4o'      => ['input' => 0.005,  'output' => 0.015],
                'gpt-4-turbo' => ['input' => 0.010,  'output' => 0.030],
                'gpt-3.5-turbo'=> ['input' => 0.0005,'output' => 0.0015],
            ],
        ],

        'claude' => [
            'api_key'       => env('CLAUDE_API_KEY'),
            'default_model' => env('CLAUDE_DEFAULT_MODEL', 'claude-3-5-sonnet-20241022'),
            'models'        => ['claude-3-5-sonnet-20241022', 'claude-3-opus-20240229', 'claude-3-haiku-20240307'],
            'base_url'      => 'https://api.anthropic.com/v1',
            'api_version'   => '2023-06-01',
            'timeout'       => 90,
            'pricing' => [
                'claude-3-5-sonnet-20241022' => ['input' => 0.003,   'output' => 0.015],
                'claude-3-opus-20240229'     => ['input' => 0.015,   'output' => 0.075],
                'claude-3-haiku-20240307'    => ['input' => 0.00025, 'output' => 0.00125],
            ],
        ],

        'gemini' => [
            'api_key'       => env('GEMINI_API_KEY'),
            'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-1.5-pro'),
            'models'        => ['gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-2.0-flash'],
            'base_url'      => 'https://generativelanguage.googleapis.com/v1beta',
            'timeout'       => 90,
            'pricing' => [
                'gemini-1.5-pro'  => ['input' => 0.00125, 'output' => 0.005],
                'gemini-1.5-flash'=> ['input' => 0.000075,'output' => 0.0003],
                'gemini-2.0-flash'=> ['input' => 0.0001,  'output' => 0.0004],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failover
    |--------------------------------------------------------------------------
    */
    'failover' => [
        'enabled'     => env('AI_FAILOVER_ENABLED', true),
        'order'       => explode(',', env('AI_FAILOVER_ORDER', 'openai,claude,gemini')),
        'max_retries' => (int) env('AI_MAX_RETRIES', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage / Billing
    |--------------------------------------------------------------------------
    */
    'usage' => [
        'monthly_token_limit' => (int) env('AI_MONTHLY_TOKEN_LIMIT', 100_000),
        'warning_threshold'   => 0.80, // Warn at 80 % of monthly limit
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'connection' => env('AI_QUEUE_CONNECTION', 'redis'),
        'name'       => env('AI_QUEUE_NAME', 'ai'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'templates_ttl' => 3_600,   // seconds
        'usage_ttl'     => 300,
    ],

];
