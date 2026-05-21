<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Categories
    |--------------------------------------------------------------------------
    | Each category maps to a group of notification types. Users can toggle
    | per-category, per-channel preferences. The label and icon are used in
    | the preferences UI.
    */
    'categories' => [
        'post'      => ['label' => 'Posts',           'icon' => 'edit'],
        'media'     => ['label' => 'Media Library',   'icon' => 'image'],
        'analytics' => ['label' => 'Analytics',       'icon' => 'bar-chart'],
        'social'    => ['label' => 'Social Accounts', 'icon' => 'share-2'],
        'team'      => ['label' => 'Team',            'icon' => 'users'],
        'billing'   => ['label' => 'Billing',         'icon' => 'credit-card'],
        'system'    => ['label' => 'System',          'icon' => 'bell'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    | database  — in-app bell
    | mail      — email via Laravel's notification system
    | broadcast — real-time WebSocket push (Pusher)
    | push      — browser/mobile push via FCM
    | whatsapp  — WhatsApp via Twilio
    | slack     — workspace Slack integration
    | sms       — SMS via Twilio
    */
    'channels' => ['database', 'mail', 'broadcast', 'push', 'whatsapp', 'slack', 'sms'],

    /*
    |--------------------------------------------------------------------------
    | Channel labels shown in the preferences UI
    */
    'channel_labels' => [
        'database'  => 'In-app',
        'mail'      => 'Email',
        'broadcast' => 'Live',
        'push'      => 'Push',
        'whatsapp'  => 'WhatsApp',
        'slack'     => 'Slack',
        'sms'       => 'SMS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default channel state (true = on)
    | A missing preference record means the default applies.
    */
    'defaults' => [
        'database'  => true,
        'mail'      => true,
        'broadcast' => true,
        'push'      => false,
        'whatsapp'  => false,
        'slack'     => false,
        'sms'       => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention (days) — notifications older than this are pruned
    */
    'retention_days' => (int) env('NOTIFICATION_RETENTION_DAYS', 60),

    /*
    |--------------------------------------------------------------------------
    | Digest email schedule: 'daily' | 'weekly' | false
    */
    'digest_schedule' => env('NOTIFICATION_DIGEST', 'weekly'),

    /*
    |--------------------------------------------------------------------------
    | Bell dropdown: number of notifications shown
    */
    'bell_limit' => 8,
];
