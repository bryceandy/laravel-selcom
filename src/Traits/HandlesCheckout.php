<?php

namespace Bryceandy\Selcom\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

trait HandlesCheckout
{
    public function checkout(array $data)
    {
        $this->validateCheckoutData($data);

        $orderId = $this->generateOrderId();

        $orderRequest = $this->makeRequest(
            'checkout/create-order-minimal',
            'POST',
            $this->getMinimalOrderData($data, $orderId)
        );

        return $this->handleCheckoutOrderResponse($orderRequest, $data, $orderId);
    }

    public function cardCheckout(array $data)
    {
        $this->validateCardCheckoutData($data);

        $orderId = $this->generateOrderId();

        $orderRequest = $this->makeRequest(
            'checkout/create-order',
            'POST',
            array_merge(
                $this->getMinimalOrderData($data, $orderId),
                $this->getCardCheckoutExtraData($data),
                (! blank($data['user_id']) ? ['buyer_userid' => $data['user_id']] : []),
                (! blank($data['buyer_uuid']) ? ['gateway_buyer_uuid' => $data['buyer_uuid']] : [])
            )
        );

        return $this->handleCardCheckoutOrderResponse($orderRequest, $data, $orderId);
    }

    private function generateOrderId(): string
    {
        return (string) Str::of($this->prefix())->snake('')->upper()
            . now()->timestamp
            . rand(1, 9999);
    }

    private function getMinimalOrderData(array $data, string $orderId): array
    {
        return [
            'vendor' => $this->vendor,
            'order_id' => $orderId,
            'buyer_email' => $data['email'],
            'buyer_name' => $data['name'],
            'buyer_phone' => $data['phone'],
            'amount' => (int) $data['amount'],
            'currency' => $data['currency'] ?? 'TZS',
            'redirect_url' => base64_encode($this->redirectUrl()),
            'cancel_url' => base64_encode($this->cancelUrl()),
            'webhook' => base64_encode(route('selcom.checkout-callback')),
            'no_of_items' => (int) ($data['items'] ?? 1),
            'expiry' => $this->paymentExpiry(),
            'header_colour' => $this->paymentGatewayColors()['header'],
            'link_colour' => $this->paymentGatewayColors()['link'],
            'button_colour' => $this->paymentGatewayColors()['button'],
        ];
    }

    private function checkForResponseFailure($response)
    {
        if ($response->failed()) {
            return $response->json();
        }
    }

    private function redirectToPaymentPage($response)
    {
        return redirect(base64_decode($response['data'][0]['payment_gateway_url']));
    }

    private function getCardCheckoutExtraData(array $data): array
    {
        return [
            'payment_methods' => 'ALL',
            'billing.firstname' => explode(' ', $data['name'])[0],
            'billing.lastname' => explode(' ', $data['name'])[1],
            'billing.address_1' => $data['address'],
            'billing.city' => $data['city'] ?? 'Dar Es Salaam',
            'billing.state_or_region' => $data['state'] ?? 'Dar Es Salaam',
            'billing.postcode_or_pobox' => $data['postcode'],
            'billing.country' => $data['country_code'] ?? 'TZ',
            'billing.phone' => $data['billing_phone'] ?? $data['phone'],
        ];
    }

    private function handleCheckoutOrderResponse(Response $response, array $data, string $orderId)
    {
        $this->checkForResponseFailure($response);

        return $data['is_ussd'] ?? false
            ? $this->makeRequest('checkout/wallet-payment', 'POST', [
                'transid' => $data['transaction_id'],
                'order_id' => $orderId,
                'msisdn' => $data['payment_phone'] ?? $data['phone'],
            ])
                ->json()
            : $this->redirectToPaymentPage($response);
    }

    private function handleCardCheckoutOrderResponse(Response $response, array $data, string $orderId)
    {
        $this->checkForResponseFailure($response);

        return $data['no_redirection'] ?? false
            ? collect([])
            : $this->redirectToPaymentPage($response);
    }
}
