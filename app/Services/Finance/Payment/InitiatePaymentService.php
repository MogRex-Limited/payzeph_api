<?php

namespace App\Services\Finance\Payment;

use App\Constants\Finance\PaymentConstants;
use App\Constants\Finance\TransactionActivityConstants;
use App\Constants\Finance\TransactionConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\Finance\Payment\PaymentException;
use App\Exceptions\Finance\Payment\SquadException;
use App\Helpers\MethodsHelper;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Finance\Payment\PaymentService;
use App\Services\Finance\Pricing\PricingService;
use App\Services\Finance\Provider\Squad\SquadService;
use App\Services\Finance\Transaction\TransactionService;
use App\Services\System\ExceptionService;
use Exception;
use Illuminate\Support\Facades\DB;

class InitiatePaymentService extends PaymentService
{
    public $gateway;
    public $user;
    public $amount;
    public $unit;
    public $transaction;
    public $description;
    public $narration;
    public $pricing_service;
    public $batch_no;

    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->user = $user;
        $this->pricing_service = new PricingService;
    }

    public function setPayload($payload)
    {
        $this->validated_payload = $payload;
        $this->amount = $payload;
        $this->unit = $this->calculateUnit($payload["amount"]);
        return $this;
    }

    public function calculateUnit($amount)
    {
        $pricing = $this->pricing_service->calculate([
            "quantity" => $amount,
            "unit" => "money"
        ]);

        $unit = $pricing["unit"];
        return $unit;
    }

    public function setBatchNo($batch_no)
    {
        $this->batch_no = $batch_no;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setNarration($narration)
    {
        $this->narration = $narration;
        return $this;
    }

    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    public function parseFee($amount)
    {
        return 0;
    }

    public function byGateway(): array
    {
        if (!in_array($this->gateway, [PaymentConstants::GATEWAY_SQUAD])) {
            throw new PaymentException("Gateway is currently inactve");
        }

        if ($this->gateway == PaymentConstants::GATEWAY_SQUAD) {
            $initializeData = self::withSquad();
        }

        return $initializeData;
    }

    public function withSquad()
    {
        DB::beginTransaction();
        try {
            $amount = $this->amount;
            $fee = $this->parseFee($amount);
            $billing_amount = $amount + $fee;
            $squadService = new SquadService;

            $old_sender_wallet_balance = TransactionService::oldWalletBalance($this->wallet);
            $this->transaction = $transaction = TransactionService::create(array_merge([
                "user_id" => $this->user->id,
                "wallet_id" => $this->wallet->id,
                "currency_id" => $this->wallet->currency_id,
                "amount" => $amount,
                "fees" => $fee,
                "description" => $this->description,
                "unit_quantity" => $this->unit,
                "reference" => $this->generateTransactioneferenceNo(),
                "activity" => TransactionActivityConstants::PAYMENT_WITH_SQUAD_VIA_WEB,
                "batch_no" => $this->batch_no ?? TransactionService::generateBatchNo(),
                "type" => TransactionConstants::DEBIT,
                "status" => StatusConstants::PENDING,
                "action" => TransactionConstants::MONEY_SENT,
                "prev_wallet_balance" => $old_sender_wallet_balance["balance"],
                "new_wallet_balance" => $old_sender_wallet_balance["balance"],
                "metadata" => json_encode(array_merge($this->validated_payload["metadata"] ?? [], [
                    "wallet" => [
                        "old" => $old_sender_wallet_balance,
                        "new" => $old_sender_wallet_balance
                    ]
                ])),
            ]));

            $metadata = [
                "user_id" => $this->user->id,
                "transaction_id" => $this->transaction->id,
                "currency" => $this->wallet->currency->short_name,
                "original_aamount" => $amount,
                "billing_amount" => $billing_amount,
                "activity" => $transaction->activity,
            ];

            Payment::create([
                "user_id" => $this->user->id,
                "currency_id" => $this->wallet->currency->id,
                "reference" => $transaction->reference,
                "transaction_id" => $transaction->id,
                "amount" => $billing_amount,
                "fee" => $transaction->fees,
                "action" => PaymentConstants::PAYMENT,
                "metadata" => json_encode([
                    "payload" => $metadata ?? null
                ]),
                "activity" => PaymentConstants::WEB_PAYMENT_FOR_UNIT,
                "gateway" => $this->gateway,
            ]);

            $initializeData = $squadService
                ->setCurrency($metadata["currency"])
                ->setCustomerData([
                    "email" => $this->user->email,
                    "name" => $this->user->full_name
                ])
                ->setMetadata($metadata)
                ->initialize($transaction->reference, $billing_amount);

            if (blank($initializeData)) {
                throw new SquadException("No response from squad");
            }

            if (($initializeData["data"]["success"] ?? null) != true) {
                throw new SquadException($initializeData["data"]["message"] ?? $initializeData["message"]);
            }

            DB::commit();
            $metadata["currency"] = $this->wallet->currency->minimal();
            return [
                "link" => $initializeData["data"]["data"]["checkout_url"],
                "reference" => $initializeData["data"]["data"]["transaction_ref"],
                "paymentReference" => $initializeData["data"]["data"]["transaction_ref"],
                "currency" => $this->wallet->currency,
                "amount" => $amount,
                "transaction" => $transaction,
                "user" => $this->user,
                "success" => true,
                "message" => $initializeData["data"]["message"],
                "metadata" => $metadata,
            ];
        } catch (Exception $e) {
            DB::rollback();
            ExceptionService::logAndBroadcast($e);
            throw new SquadException("Couldn`t initiate payment. Please contact our support +2349124939597");
        }
    }

    public function generateTransactioneferenceNo($length = 10)
    {
        $merchant_id = strtoupper(str_replace("-", "", $this->model->uuid));
        $key = "$merchant_id" . "-" . MethodsHelper::getRandomToken($length, true);

        $check = Transaction::where("reference", $key)->count();
        if ($check > 0) {
            return self::generateTransactioneferenceNo();
        }

        return $key;
    }
}
