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
   | A value that determines if you are using Selcom in test mode or live.
   | When testing, the above values would be different in every stage.
   |
   | For example; the SELCOM_API_SECRET in test mode is different from
   | SELCOM_API_SECRET in live mode. The same applies to all others.
   */
    'live' => env('SELCOM_IS_LIVE', false),
];
