<?php

namespace App\Services\Finance\Payment;

use App\Constants\Finance\PaymentConstants;
use App\Models\Wallet;
use App\Services\Finance\Payment\InitiatePaymentService;
use App\Services\Finance\Payment\BasePaymentService;

class PaymentService extends BasePaymentService
{
    public $validated_payload;
    public $response;
    public Wallet $wallet;
    public $model;

    public function __construct($user)
    {
        parent::__construct($user);
    }

    public function initiate()
    {
        $this->actionHandler();
        return $this->parseResponse();
    }

    public function actionHandler()
    {
        $response = (new InitiatePaymentService($this->user))
            ->setPayload($this->validated_payload)
            ->setDescription($this->description ?? "Payment of funds by {$this->user->full_name}")
            ->setGateway(PaymentConstants::GATEWAY_SQUAD)
            ->byGateway();

        $this->response = $response;
        return $response;
    }

    public function parseResponse()
    {
        return [
            "payment_url" => $this->response["link"],
            "reference" => $this->response["reference"]
        ];
    }
}
