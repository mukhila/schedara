<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'microsoft' => [
        'client_id'     => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect'      => env('MICROSOFT_REDIRECT_URI', '/auth/microsoft/callback'),
        'tenant'        => env('MICROSOFT_TENANT_ID', 'common'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI', '/auth/facebook/callback'),
    ],

    // ── Social Integration OAuth (separate callback URLs from auth OAuth) ──

    'linkedin-openid' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('LINKEDIN_REDIRECT_URI', '/social/callback/linkedin'),
    ],

    'twitter-oauth-2' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT_URI', '/social/callback/twitter'),
    ],

    'pinterest' => [
        'client_id'     => env('PINTEREST_CLIENT_ID'),
        'client_secret' => env('PINTEREST_CLIENT_SECRET'),
        'redirect'      => env('PINTEREST_REDIRECT_URI', '/social/callback/pinterest'),
    ],

    'threads' => [
        'client_id'     => env('THREADS_CLIENT_ID'),
        'client_secret' => env('THREADS_CLIENT_SECRET'),
        'redirect'      => env('THREADS_REDIRECT_URI', '/social/callback/threads'),
    ],

    // Instagram Business uses the Facebook OAuth driver — shares FB credentials
    'instagram' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('INSTAGRAM_REDIRECT_URI', '/social/callback/instagram'),
    ],

    'tiktok' => [
        'client_id'     => env('TIKTOK_CLIENT_ID'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'redirect'      => env('TIKTOK_REDIRECT_URI', '/social/callback/tiktok'),
    ],

    // YouTube uses the Google OAuth driver — shares Google credentials
    'youtube' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('YOUTUBE_REDIRECT_URI', '/social/callback/youtube'),
    ],

    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'razorpay' => [
        'key_id'         => env('RAZORPAY_KEY_ID'),
        'key_secret'     => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'billing' => [
        'default_provider' => env('BILLING_DEFAULT_PROVIDER', 'stripe'),
    ],

    // ── Twilio (SMS + WhatsApp) ────────────────────────────────────────
    'twilio' => [
        'sid'            => env('TWILIO_SID'),
        'token'          => env('TWILIO_AUTH_TOKEN'),
        'sms_from'       => env('TWILIO_SMS_FROM'),
        'whatsapp_from'  => env('TWILIO_WHATSAPP_FROM'),
    ],

    // ── Firebase Cloud Messaging (FCM) ────────────────────────────────
    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'server_key' => env('FCM_SERVER_KEY'),
    ],

];
