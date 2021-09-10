<?php

use Bryceandy\Selcom\Http\Controllers\CheckoutCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('checkout-callback', CheckoutCallbackController::class)
    ->name('selcom.checkout-callback');;

Route::view('redirect', 'selcom::redirect')->name('selcom.redirect');

Route::view('cancel', 'selcom::cancel')->name('selcom.cancel');
