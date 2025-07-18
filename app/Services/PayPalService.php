<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayPalService
{
    private $clientId;
    private $clientSecret;
    private $baseUrl;

    public function __construct($clientId, $clientSecret, $isSandbox = true)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public function getAccessToken()
    {
        $url = $this->baseUrl . '/v1/oauth2/token';

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post($url, [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('فشل الحصول على رمز الوصول PayPal: ' . $response->body());
    }

    public function createOrder($amount)
    {
        $accessToken = $this->getAccessToken();

        $url = $this->baseUrl . '/v2/checkout/orders';
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($amount, 2, '.', '')
                ]
            ]]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'PayPal-Request-Id' => uniqid() 
        ])->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('فشل إنشاء طلب PayPal: ' . $response->body());
    }

    public function capturePayment($orderId)
    {
        $accessToken = $this->getAccessToken();
        $url = $this->baseUrl . "/v2/checkout/orders/{$orderId}/capture";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'PayPal-Request-Id' => uniqid()
        ])->post($url);

        return $response->json();
    }
}
