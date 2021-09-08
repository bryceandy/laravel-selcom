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
];
