<?php

namespace Bryceandy\Selcom\Traits;

use Bryceandy\Selcom\Exceptions\InvalidDataException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
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

        return $this->handleOrderResponse($orderRequest, $data, $orderId);
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
                (($data['user_id'] ?? false) ? ['buyer_userid' => $data['user_id']] : []),
                (($data['buyer_uuid'] ?? false) ? ['gateway_buyer_uuid' => $data['buyer_uuid']] : [])
            )
        );

        return $this->handleOrderResponse($orderRequest, $data, $orderId, true);
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

    private function checkRequestFailure(Response $response)
    {
        if ($response->failed()) {
            return $response->json();
        }
    }

    /**
     * @throws InvalidDataException
     */
    private function handleOrderResponse(Response $response, array $data, string $orderId, $cardPayment = false)
    {
        $this->checkRequestFailure($response);

        $gatewayBuyerUuid = $data['buyer_uuid'] ?? $response['data'][0]['gateway_buyer_uuid'] ?? null;

        DB::table('selcom_payments')->insert(array_merge(
            [
                'amount' => (int) $data['amount'],
                'order_id' => $orderId,
                'transid' => $data['transaction_id'],
                'created_at' => now(),
            ],
            ($gatewayBuyerUuid ? ['gateway_buyer_uuid' => $gatewayBuyerUuid] : []),
            (($data['user_id'] ?? false) ? ['user_id' => $data['user_id']] : []),
        ));

        if ($data['no_redirection'] ?? false) {
            return $cardPayment
                ? $this->makeCardPayment($data, $orderId, $gatewayBuyerUuid)
                : $this->makeWalletPullPayment($data, $orderId);
        }

        $url = base64_decode($response['data'][0]['payment_gateway_url']);

        return request()->expectsJson()
            ? response()->json(['payment_gateway_url' => $url])
            : redirect($url);
    }

    private function makeWalletPullPayment(array $data, string $orderId)
    {
        return $this->makeRequest('checkout/wallet-payment', 'POST', [
            'transid' => $data['transaction_id'],
            'order_id' => $orderId,
            'msisdn' => $data['payment_phone'] ?? $data['phone'],
        ])
            ->json();
    }

    /**
     * @throws InvalidDataException
     */
    private function makeCardPayment(array $data, string $orderId, $gatewayBuyerUuid)
    {
        if (is_null($gatewayBuyerUuid)) {
            throw new InvalidDataException(
                'Provide the Buyer UUID for this user before making the request!'
            );
        }

        $fetchCards = $this->makeRequest('checkout/stored-cards', 'GET', [
            'buyer_userid' => $data['user_id'],
            'gateway_buyer_uuid' => $gatewayBuyerUuid,
        ]);

        $this->checkRequestFailure($fetchCards);

        if (! count($fetchCards['data'])) {
            throw new InvalidDataException("User doesn't have stored cards!");
        }

        return rescue(
            fn() => $this->cardPayment($fetchCards['data'][0]['card_token'], $data, $orderId, $gatewayBuyerUuid),
            function () use ($fetchCards, $data, $orderId, $gatewayBuyerUuid) {
                if (count($fetchCards['data']) > 1) {
                    return rescue(
                        fn() => $this->cardPayment($fetchCards['data'][1]['card_token'], $data, $orderId, $gatewayBuyerUuid),
                        fn() => count($fetchCards['data']) > 2
                            ? $this->cardPayment($fetchCards['data'][2]['card_token'], $data, $orderId, $gatewayBuyerUuid)
                            : null
                    );
                }

                return null;
            }
        );
    }

    private function cardPayment(string $cardToken, array $data, string $orderId, $uuid)
    {
        return $this->makeRequest('checkout/card-payment', 'POST', [
            'transid' => $data['transaction_id'],
            'vendor' => $this->vendor,
            'order_id' => $orderId,
            'card_token' => $cardToken,
            'buyer_userid' => $data['user_id'],
            'gateway_buyer_uuid' => $uuid,
        ])
            ->json();
    }

    public function fetchCards($userId, $buyerUuid)
    {
        return $this->makeRequest('checkout/stored-cards', 'GET', [
            'buyer_userid' => $userId,
            'gateway_buyer_uuid' => $buyerUuid,
        ])
            ->json();
    }

    public function deleteCard($cardId, $buyerUuid)
    {
        return $this->makeRequest('checkout/delete-card', 'DELETE', [
            'id' => $cardId,
            'gateway_buyer_uuid' => $buyerUuid,
        ])
            ->json();
    }

    public function processCheckoutWebhook()
    {
        DB::table('selcom_payments')
            ->where('transid', request('transid'))
            ->where('order_id', request('order_id'))
            ->update([
                'reference' => request('reference'),
                'payment_status' => request('payment_status'),
                'updated_at' => now(),
            ]);
    }

    public function orderStatus($orderId)
    {
        return $this->makeRequest('checkout/order-status', 'GET', [
            'order_id' => $orderId,
        ])
            ->json();
    }

    public function listOrders(string $fromDate, string $toDate)
    {
        return $this->makeRequest(
            'checkout/list-orders',
            'GET',
            compact('fromDate', 'toDate')
        )
            ->json();
    }

    public function cancelOrder($orderId)
    {
        return $this->makeRequest('checkout/cancel-order', 'DELETE', [
            'order_id' => $orderId,
        ])
            ->json();
    }
}
