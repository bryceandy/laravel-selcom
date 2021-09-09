<?php

namespace Bryceandy\Selcom\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

trait HandlesCheckout
{
    public function checkout(array $data)
    {
        $this->validateCheckoutData($data);

        $orderId = (string) Str::of($this->prefix())->snake('-')->upper()
            . now()->timestamp
            . rand(1, 9999);

        $orderRequest = $this->makeRequest('checkout/create-order-minimal', 'POST', [
            'vendor' => $this->vendor,
            'order_id' => $orderId,
            'buyer_email' => $data['email'],
            'buyer_name' => $data['name'],
            'buyer_phone' => $data['phone'],
            'amount' => (int) $data['amount'],
            'currency' => $data['currency'] ?? 'TZS',
            'webhook' => base64_encode(route('selcom.checkout-callback')),
            'no_of_items' => (int) $data['items'] ?? 1,
        ]);

        return $this->handleCheckoutOrderResponse($orderRequest, $data, $orderId);
    }

    public function checkoutCard()
    {
        //
    }

    private function handleCheckoutOrderResponse(Response $response, array $data, string $orderId)
    {
        if ($response->failed()) {
            return $response->json();
        }

        return $data['is_ussd'] ?? false
            ? $this->makeRequest('checkout/wallet-payment', 'POST', [
                'transid' => $data['transaction_id'],
                'order_id' => $orderId,
                'msisdn' => $data['payment_phone'] ?? $data['phone'],
            ])
                ->json()
            : redirect(base64_decode($response['data'][0]['payment_gateway_url']));
    }
}
