<?php

namespace App\Services\Finance\Provider\Squad;

use App\Constants\Finance\PaymentConstants;
use App\Constants\Finance\PlanConstants;
use App\Constants\General\ApiConstants;
use App\Exceptions\Finance\Payment\SquadException;
use App\Services\General\Guzzle\GuzzleService;
use App\Services\System\ExceptionService;
use Exception;

class SquadService
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
        $this->base_url = $url ?? env("SQUAD_BASE_URL", 'https://sandbox-api-d.squadco.com');
        return $this;
    }

    public function setApiKey($key = null)
    {
        $this->api_key = $key ?? env("SQUAD_SECRET_KEY", 'sandbox_sk_343cec5ce3eb66e29a0f0a3de01e06e0bda3fdd0e79c');
        return $this;
    }

    public function setAdminId($merchant_id = null)
    {
        $this->merchant_id = $merchant_id ?? env("SQUAD_STAGING_MERCHANT_ID", 'SBKZCM5T8Y');
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
        if (!in_array($value, PaymentConstants::SQUAD_SUPPORTED_CURRENCIES)) {
            throw new SquadException("$value is currently not supported by Squad");
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
                throw new SquadException($response["message"] ?? null);
            }

            return [
                "amount" => ($response["data"]["data"]["balance"] / 100),
                "currency" => ($response["data"]["data"]["currency_id"] ?? "NGN")
            ];
        } catch (Exception $e) {
            ExceptionService::logAndBroadcast($e);
        }
    }

    public function verifyAcount($bank_code, $account_number, $account_name)
    {
        try {
            $full_url = $this->base_url . "/payout/account/lookup";

            $this->client = $this->setClient();

            $response = $this->client->post($full_url, [
                "bank_code" => $bank_code,
                "account_number" => $account_number,
            ]);

            logger("account lookup response", [$response]);
            if (!in_array(($response["data"]["status"] ?? null), [ApiConstants::GOOD_REQ_CODE])) {
                throw new SquadException(($response["message"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (($response["data"]["data"]["account_name"] ?? null) !== $account_name) {
                throw new SquadException("Invalid account name provided", ApiConstants::BAD_REQ_ERR_CODE);
            }

            if (($response["data"]["data"]["account_number"] ?? null) !== $account_name) {
                throw new SquadException("Invalid account number provided", ApiConstants::BAD_REQ_ERR_CODE);
            }

            $response["data"]["data"]["bank_code"] = $bank_code;

            return $response["data"]["data"];
        } catch (SquadException $e) {
            ExceptionService::broadcastOnAllChannels($e);
            throw new SquadException($e->getMessage(), $e->getCode());
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
            throw new SquadException(($response["message"]["message"] ?? $response["message"]), $response["status"]);
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
            "email" => $this->customer_data["email"],
            "amount" => (string) ($amount * 100), // Amount is multiplied because paystack uses Kobo
            "initiate_type" => "inline",
            "currency" => $this->currency,
            "transaction_ref" => $tx_ref,
            // "callback_url" => $redirectUrl,
            "customer_name" => $this->customer_data["name"],
            "payment_channels" => $this->payment_channels ?? ["card", "transfer"],
            "metadata" => $this->metadata,
        ];

        if ($payment_type == PlanConstants::RECURRENT && in_array("card", $data["payment_channels"])) {
            $data["is_recurring"] = true;
        }

        if (empty($data["email"])) {
            throw new SquadException("Customer data is required");
        }

        if (empty($data["transaction_ref"])) {
            throw new SquadException("Transaction ref is required");
        }

        $full_url = $this->base_url . "/transaction/initiate";

        $response = (new GuzzleService($this->headers))->post($full_url, $data);

        return $response;
    }


    public function chargeCardViaToken(string $tx_ref, float $amount)
    {
        $data = [
            "amount" => (string) ($amount * 100), // Amount is multiplied because paystack uses Kobo
            "transaction_ref" => $tx_ref,
            "token_id" => $this->card_token,
        ];

        if (empty($data["token_id"] ?? null)) {
            throw new SquadException("Card token is required");
        }

        if (empty($data["transaction_ref"] ?? null)) {
            throw new SquadException("Transaction ref is required");
        }

        $full_url = $this->base_url . "/transaction/charge_card";

        logger("Charge card payload", [
            "url" => $full_url,
            "payload" => $data
        ]);
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
