<?php

namespace Bryceandy\Selcom\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CheckoutWebhookReceived
{
    use Dispatchable, InteractsWithSockets;

    public string $orderId;

    /**
     * @param string $orderId
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }
}