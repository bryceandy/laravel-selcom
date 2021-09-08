<?php

use Bryceandy\Selcom\Http\Controllers\CheckoutCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('checkout-callback', CheckoutCallbackController::class)
    ->name('selcom.checkout-callback');;
