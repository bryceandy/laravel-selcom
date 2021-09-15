<?php

namespace Bryceandy\Selcom\Http\Controllers;

use Bryceandy\Selcom\Facades\Selcom;
use Illuminate\Routing\Controller;

class CheckoutCallbackController extends Controller
{
    public function __invoke()
    {
        return Selcom::processCheckoutWebhook();
    }
}