<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('BILLING_DEFAULT_PROVIDER', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('APP_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Tax Configuration
    |--------------------------------------------------------------------------
    | tax_rate: applied when currency is not INR/EUR (generic fallback)
    | gst_rate: applied when currency is INR (India GST 18%)
    | vat_rate: applied when currency is EUR
    */
    'tax_rate' => env('BILLING_TAX_RATE', 0),
    'gst_rate' => env('BILLING_GST_RATE', 18),
    'vat_rate' => env('BILLING_VAT_RATE', 20),

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    */
    'invoice_prefix'   => env('BILLING_INVOICE_PREFIX', 'INV'),
    'invoice_due_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Trial
    |--------------------------------------------------------------------------
    */
    'grace_days' => 3,

    /*
    |--------------------------------------------------------------------------
    | Failed Payment Retry Schedule (days after initial failure)
    |--------------------------------------------------------------------------
    */
    'retry_schedule' => [1, 3, 7],

    /*
    |--------------------------------------------------------------------------
    | Stripe
    |--------------------------------------------------------------------------
    */
    'stripe' => [
        'key'             => env('STRIPE_KEY'),
        'secret'          => env('STRIPE_SECRET'),
        'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Razorpay
    |--------------------------------------------------------------------------
    */
    'razorpay' => [
        'key_id'         => env('RAZORPAY_KEY_ID'),
        'key_secret'     => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal
    |--------------------------------------------------------------------------
    */
    'paypal' => [
        'client_id'  => env('PAYPAL_CLIENT_ID'),
        'secret'     => env('PAYPAL_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        'sandbox'    => env('PAYPAL_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    */
    'currencies' => [
        'USD' => ['symbol' => '$',    'label' => 'US Dollar',        'gateway' => 'stripe'],
        'EUR' => ['symbol' => '€',    'label' => 'Euro',             'gateway' => 'stripe'],
        'GBP' => ['symbol' => '£',    'label' => 'British Pound',    'gateway' => 'stripe'],
        'AED' => ['symbol' => 'د.إ',  'label' => 'UAE Dirham',       'gateway' => 'stripe'],
        'INR' => ['symbol' => '₹',    'label' => 'Indian Rupee',     'gateway' => 'razorpay'],
    ],

];
