<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Queue
    |--------------------------------------------------------------------------
    */
    'queue' => env('ANALYTICS_QUEUE', 'analytics'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => (int) env('ANALYTICS_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Viral Post Threshold
    | Posts with engagement_rate >= this value trigger ViralPostDetected event
    |--------------------------------------------------------------------------
    */
    'viral_threshold' => (float) env('ANALYTICS_VIRAL_THRESHOLD', 5.0),

    /*
    |--------------------------------------------------------------------------
    | ROI Alert Threshold (%)
    | Campaigns with ROI >= this value trigger ROIThresholdReached event
    |--------------------------------------------------------------------------
    */
    'roi_alert_threshold' => (float) env('ANALYTICS_ROI_ALERT_THRESHOLD', 200.0),

    /*
    |--------------------------------------------------------------------------
    | AI Predictive Analytics
    |--------------------------------------------------------------------------
    */
    'ai_analysis_enabled' => env('ANALYTICS_AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Sync Schedule
    |--------------------------------------------------------------------------
    */
    'sync_interval_minutes' => (int) env('ANALYTICS_SYNC_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | Report Storage
    |--------------------------------------------------------------------------
    */
    'report_disk'       => env('ANALYTICS_REPORT_DISK', 'local'),
    'report_path'       => env('ANALYTICS_REPORT_PATH', 'analytics/reports'),
    'report_expiry_days'=> (int) env('ANALYTICS_REPORT_EXPIRY_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Bitly URL Shortening
    | If not set, falls back to local /r/{shortCode} URLs
    |--------------------------------------------------------------------------
    */
    'bitly_api_key' => env('BITLY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Log Retention (days)
    | analytics_logs older than this are deleted by CleanupAnalyticsLogsJob
    |--------------------------------------------------------------------------
    */
    'log_retention_days' => (int) env('ANALYTICS_LOG_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Platform API Keys (set per-tenant via DB, these are defaults)
    |--------------------------------------------------------------------------
    */
    'google_analytics_property' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
    'facebook_app_id'           => env('FACEBOOK_APP_ID'),
    'facebook_app_secret'       => env('FACEBOOK_APP_SECRET'),
];
