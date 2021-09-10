<?php

namespace Bryceandy\Selcom;

use Bryceandy\Selcom\Exceptions\MissingDataException;
use Bryceandy\Selcom\Traits\HandlesCheckout;
use Bryceandy\Selcom\Traits\ValidatesData;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Selcom
{
    use HandlesCheckout;
    use ValidatesData;

    private $vendor;

    private $apiKey;

    private $apiSecret;

    private string $apiUrl;

    public function __construct()
    {
        $this->vendor = config('selcom.vendor');

        $this->apiKey = config('selcom.key');

        $this->apiSecret = config('selcom.secret');

        $subdomain = config('selcom.live') ? 'apigw' : 'apigwtest';

        $this->apiUrl = "https://$subdomain.selcommobile.com/v1/";
    }

    public function prefix()
    {
        return config('selcom.prefix');
    }

    public function redirectUrl()
    {
        return config('selcom.redirect_url') ?? route('selcom.redirect');
    }

    public function cancelUrl()
    {
        return config('selcom.cancel_url') ?? route('selcom.cancel');
    }

    public function paymentGatewayColors()
    {
        return config('selcom.colors');
    }

    public function paymentExpiry()
    {
        return config('selcom.expiry');
    }

    /**
     * @throws MissingDataException
     */
    private function validateConfig()
    {
        if (! config('selcom.vendor') || ! config('selcom.key') || ! config('selcom.secret')) {
            throw new MissingDataException(
                'Your Selcom credentials can not be empty!'
            );
        }
    }

    public function makeRequest(string $uri, string $method, array $data = []): Response
    {
        $fullPath = $this->apiUrl . $uri;

        return Http::withHeaders($this->getHeaders($data))
            ->{strtolower($method)}($fullPath, $data);
    }

    private function getHeaders($data): array
    {
        $this->validateConfig();

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $authorization = base64_encode($this->apiKey);
        $signedFields = implode(',', array_keys($data));
        $timestamp = date('c');
        $digest = $this->getDigest($data, $timestamp);

        return [
            'Content-type' => 'application/json;charset=\"utf-8\"',
            'Accept' => 'application/json',
            'Authorization' => "SELCOM $authorization",
            'Digest-Method' => 'HS256',
            'Digest' => $digest,
            'Signed-Fields' => $signedFields,
            'Cache-Control' => 'no-cache',
            'Timestamp' => $timestamp,
        ];
    }

    private function getDigest($data, $timestamp): string
    {
        $this->validateConfig();

        $signData = "timestamp=$timestamp";

        if (count($data)) {
            foreach ($data as $key => $value) {
                $signData .= "&$key=$value";
            }
        }

        return base64_encode(hash_hmac('sha256', $signData, $this->apiSecret, true));
    }
}