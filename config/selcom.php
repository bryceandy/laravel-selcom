<?php

return [

    /*
   |--------------------------------------------------------------------------
   | Vendor ID
   |--------------------------------------------------------------------------
   |
   | Float account identifier
   */
    'vendor' => env('SELCOM_VENDOR_ID'),

    /*
   |--------------------------------------------------------------------------
   | API Key
   |--------------------------------------------------------------------------
   |
   | Merchant API key
   */
    'key' => env('SELCOM_API_KEY'),

    /*
   |--------------------------------------------------------------------------
   | API Secret
   |--------------------------------------------------------------------------
   |
   | Merchant API secret
   */
    'secret' => env('SELCOM_API_SECRET'),

    /*
   |--------------------------------------------------------------------------
   | Selcom live status
   |--------------------------------------------------------------------------
   |
   | This determines if you are using Selcom in live mode.
   | The credentials would be different in every stage.
   |
   | SELCOM_API_KEY and SELCOM_API_SECRET should be
   | different when changing between live & test.
   */
    'live' => env('SELCOM_IS_LIVE', false),

    /*
   |--------------------------------------------------------------------------
   | Selcom prefix
   |--------------------------------------------------------------------------
   |
   | This prefix will be used for routes and on Selcom order IDs.
   */
    'prefix' => env('SELCOM_PREFIX', 'selcom'),

    /*
   |--------------------------------------------------------------------------
   | Redirect URL
   |--------------------------------------------------------------------------
   |
   | The URL where your users will be taken to after a payment is complete.
   | Eg: https://www.myshop.co.tz/checkout/redirect
   */
    'redirect_url' => env('SELCOM_REDIRECT_URL'),

    /*
   |--------------------------------------------------------------------------
   | Cancel URL
   |--------------------------------------------------------------------------
   |
   | The URL where your users will be taken to when they cancel the payment.
   | Eg: https://www.myshop.co.tz/checkout/cancel
   */
    'cancel_url' => env('SELCOM_CANCEL_URL'),

    /*
   |--------------------------------------------------------------------------
   | Payment Gateway Colors
   |--------------------------------------------------------------------------
   |
   | Colors for your payment gateway page.
   */
    'colors' => [
        'header' => env('SELCOM_HEADER_COLOR', '#FF0012'),
        'link' => env('SELCOM_LINK_COLOR', '#FF0012'),
        'button' => env('SELCOM_BUTTON_COLOR', '#FF0012'),
    ],

    /*
   |--------------------------------------------------------------------------
   | Payment Expiry
   |--------------------------------------------------------------------------
   |
   | Time in minutes before the payment gateway page expires.
   */
    'expiry' => env('SELCOM_PAYMENT_EXPIRY', 60)
];
