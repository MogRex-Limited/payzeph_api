<?php

namespace App\Services\Finance\Provider\CatholicPay;

use App\Constants\Finance\PaymentConstants;
use App\Constants\Finance\PlanConstants;
use App\Constants\General\ApiConstants;
use App\Exceptions\Finance\Payment\CatholicPayException;
use App\Services\General\Guzzle\GuzzleService;
use App\Services\System\ExceptionService;
use Exception;

class CatholicPayService
{
    private $env;
    public $base_url;
    public $api_key;
    public array $headers;
    public $client;
    public $customer_data;
    public $metadata;
    public $currency;
    public $card_token;
    public $payment_channels;
    public $merchant_id;

    public function __construct()
    {
        $this->env = env("APP_ENV");
        $this->setBaseUrl();
        $this->setApiKey();
        $this->setHeaders();
        $this->setAdminId();
    }

    public function setBaseUrl($url = null)
    {
        $this->base_url = $url ?? env("CATHOLICPAY_BASE_URL");
        return $this;
    }

    public function setApiKey($key = null)
    {
        $this->api_key = $key ?? env("CATHOLICPAY_SECRET_KEY");
        return $this;
    }

    public function setAdminId($merchant_id = null)
    {
        $this->merchant_id = $merchant_id ?? env("CATHOLICPAY_MERCHANT_ID");
        return $this;
    }

    public function setHeaders(?array $headers = [])
    {
        $this->headers = array_merge([
            'Authorization' => "Bearer $this->api_key",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ], $headers);
    }

    public function setClient()
    {
        $this->client = new GuzzleService($this->headers);
        return $this->client;
    }

    public function setCustomerData(array $value)
    {
        $this->customer_data = $value;
        return $this;
    }

    public function setCardToken($value)
    {
        $this->card_token = $value;
        return $this;
    }

    public function setMetadata(array $value)
    {
        $this->metadata = $value;
        return $this;
    }

    public function setCurrency(string $value)
    {
        if (!in_array($value, PaymentConstants::CATHOLICPAY_SUPPORTED_CURRENCIES)) {
            throw new CatholicPayException("$value is currently not supported by CatholicPay");
        }

        $this->currency = $value;
        return $this;
    }

    public function getBalance()
    {
        try {
            $full_url = $this->base_url . "/merchant/balance";
            $response = $this->setClient()->getWithQuery($full_url, [
                "currency_id" => "NGN"
            ]);

            if (!in_array($response["status"], [ApiConstants::GOOD_REQ_CODE])) {
                throw new CatholicPayException($response["message"] ?? null);
            }

            return $response["data"];
        } catch (Exception $e) {
            ExceptionService::logAndBroadcast($e);
        }
    }

    public function requeryTransactionByReference($reference)
    {
        $full_url = $this->base_url . "/payout/requery";
        $this->client = $this->setClient();

        $response = $this->client->post($full_url, [
            "transaction_reference" => $reference,
        ]);

        if (in_array($response["status"] ?? null, [ApiConstants::NOT_FOUND_ERR_CODE])) {
            return null;
        }

        if (!in_array($response["status"] ?? null, [ApiConstants::GOOD_REQ_CODE])) {
            throw new CatholicPayException(($response["message"]["message"] ?? $response["message"]), $response["status"]);
        }

        return $response["data"];
    }

    public function setPaymentChannels(array $channels)
    {
        $this->payment_channels = $channels;
        return $this;
    }

    public function initialize(string $tx_ref, float $amount, $payment_type = null)
    {
        $data = [
            "amount" => (string) ($amount * 100), // Amount is multiplied because catholicpay uses Kobo
            "currency" => $this->currency,
            "transaction_ref" => $tx_ref,
            "callback_url" => null,
            "email" => $this->customer_data["email"],
            "customer_name" => $this->customer_data["name"],
            "payment_channels" => $this->payment_channels ?? ["card", "transfer"],
            "metadata" => $this->metadata,
        ];

        if ($payment_type == PlanConstants::RECURRENT && in_array("card", $data["payment_channels"])) {
            $data["is_recurring"] = true;
        }

        if (empty($data["email"])) {
            throw new CatholicPayException("Customer data is required");
        }

        if (empty($data["transaction_ref"])) {
            throw new CatholicPayException("Transaction ref is required");
        }

        $full_url = $this->base_url . "/payment/initiate";

        $response = (new GuzzleService($this->headers))->post($full_url, $data);

        return $response;
    }

    public function chargeWallet(string $tx_ref, float $amount, $payment_type = null)
    {
        $data = [
            "amount" => (string) ($amount * 100), // Amount is multiplied because catholicpay uses Kobo
            "currency" => $this->currency,
            "transaction_ref" => $tx_ref,
            "email" => $this->customer_data["email"],
            "customer_name" => $this->customer_data["name"],
            "metadata" => $this->metadata,
        ];

        if (empty($data["email"])) {
            throw new CatholicPayException("Customer data is required");
        }

        if (empty($data["transaction_ref"])) {
            throw new CatholicPayException("Transaction ref is required");
        }

        $full_url = $this->base_url . "/payment/charge-wallet";

        $response = (new GuzzleService($this->headers))->post($full_url, $data);

        return $response;
    }

    public function verifyTransaction(string $transaction_id)
    {
        $full_url = $this->base_url . "/transaction/verify/$transaction_id";
        $response = (new GuzzleService($this->headers))->getWithQuery($full_url, [
            "transaction_ref" => $transaction_id
        ]);
        return $response;
    }
}
