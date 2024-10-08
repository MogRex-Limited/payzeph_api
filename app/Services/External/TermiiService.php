<?php

namespace App\Services\External;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ProviderException;
use App\Services\General\Guzzle\GuzzleService;
use App\Services\System\ExceptionService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TermiiService
{
    public $client;
    public $api_key;
    public $base_url;
    public $sender_data;
    public $messaging_data;
    public $apikey_array;
    public $validated_data;

    public function __construct()
    {
        $this->api_key = env("TERMII_API_KEY");
        $this->base_url = env("TERMII_BASE_URL");
        $this->client = new GuzzleService([
            "Content-Type" => "application/json"
        ]);
        $this->apikey_array = [
            "api_key" => $this->api_key
        ];
    }

    public function senderIdData(array $data)
    {
        $this->sender_data = $data;
        return $this;
    }

    public function messagingData(array $data)
    {
        $this->messaging_data = $data;
        return $this;
    }

    public function validate(array $data)
    {
        try {
            $rules = [];
            $data["api_key"] = $this->api_key;
            foreach ($data as $key => $value) {
                $rules[$key] = 'required';
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $this->validated_data = $validator->validated();
            return $validator->validated();
        } catch (ValidationException $th) {
            throw $th;
        }
    }


    public function createSenderId()
    {
        try {
            $full_url = $this->base_url . "/sender-id/request";

            $this->validate([
                "sender_id" => $this->sender_data["sender_id"],
                "usecase" => $this->sender_data["usecase"],
                "company" => $this->sender_data["company"],
            ]);

            $response = $this->client->post($full_url, $this->validated_data);

            if (!in_array(($response["status"] ?? null), [ApiConstants::GOOD_REQ_CODE])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (in_array(($response["data"]["status"] ?? null), [ApiConstants::BAD_REQ_ERR_CODE])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (!in_array(($response["data"]["code"] ?? null), ["ok"])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            return $response["data"];
        } catch (ProviderException $e) {
            ExceptionService::broadcastOnAllChannels($e);
            throw $e;
        }
    }

    public function sendSingleMessage()
    {
        try {
            $full_url = $this->base_url . "/sms/send";

            $data = [
                "to" => $this->messaging_data["to"],
                "from" => $this->messaging_data["from"],
                "sms" => $this->messaging_data["sms"],
                "type" => $this->messaging_data["type"],
                "channel" => $this->messaging_data["channel"],
            ];

            if (isset($this->messaging_data["media"])) {
                $data["media"] = $this->messaging_data["media"];
            }

            $this->validate($data);

            $response = $this->client->post($full_url, $this->validated_data);

            if (!in_array($response["status"] ?? null, [ApiConstants::GOOD_REQ_CODE])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (in_array($response["data"]["status"] ?? null, [ApiConstants::BAD_REQ_ERR_CODE])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (!in_array($response["data"]["code"] ?? null, ["ok"])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            return $response["data"];
        } catch (ProviderException $e) {
            ExceptionService::broadcastOnAllChannels($e);
            throw $e;
        }
    }

    public function sendBulkMessage()
    {
        try {
            $full_url = $this->base_url . "/send/bulk";

            $data = [
                "to" => $this->messaging_data["to"],
                "from" => $this->messaging_data["from"],
                "sms" => $this->messaging_data["sms"],
                "type" => $this->messaging_data["type"],
                "channel" => $this->messaging_data["channel"],
            ];

            if (isset($this->messaging_data["media"])) {
                $data["media"] = $this->messaging_data["media"];
            }

            $this->validate($data);

            $response = $this->client->post($full_url, $this->validated_data);

            if (!in_array(($response["status"] ?? null), [ApiConstants::GOOD_REQ_CODE])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (in_array(($response["data"]["status"] ?? null), [ApiConstants::BAD_REQ_ERR_CODE])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            if (!in_array(($response["data"]["code"] ?? null), ["ok"])) {
                throw new ProviderException(json_encode($response["data"]["message"] ?? $response["message"]), $response["status"]);
            }

            return $response["data"];
        } catch (ProviderException $e) {
            ExceptionService::broadcastOnAllChannels($e);
            throw $e;
        }
    }
}
