<?php
namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalService
{
    protected $provider;

    public function __construct($clientId, $clientSecret)
    {
        $this->provider = new PayPalClient;

        $config = config('paypal');

        $config['mode'] = 'sandbox';
        $config['sandbox']['client_id'] = $clientId;
        $config['sandbox']['client_secret'] = $clientSecret;

        $this->provider->setApiCredentials($config);
        $accessToken = $this->provider->getAccessToken();
        $this->provider->setAccessToken($accessToken);
    }

    public function createOrder($amount)
    {
        return $this->provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel'),
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $amount
                    ]
                ]
            ]
        ]);
    }

    public function capturePayment($orderId)
    {
        return $this->provider->capturePaymentOrder($orderId);
    }
}
