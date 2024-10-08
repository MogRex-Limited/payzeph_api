<?php

namespace App\Services\Finance\Subscription;

use App\Exceptions\Finance\Plan\SubscriptionException;
use Exception;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Constants\Finance\PaymentConstants;
use App\Constants\Finance\TransactionActivityConstants;
use App\Constants\Finance\TransactionConstants;
use App\Constants\General\ApiConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\Finance\Payment\CatholicPayException;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Finance\Transaction\TransactionService;
use App\Services\Finance\Provider\CatholicPay\CatholicPayService;
use App\Services\System\ExceptionService;

class SubscriptionInitiationWithCardService
{
    public Plan $plan;
    public User $user;
    public Currency $currency;
    public Transaction $transaction;
    public $amount, $gateway, $source, $description, $activity, $batch_no, $title;
    public $type;

    public function __construct(Plan $plan)
    {
        $this->plan = $plan;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setAmount(float $value)
    {
        $this->amount = $value;
        return $this;
    }

    public function setCurrency(Currency $value)
    {
        $this->currency = $value;
        return $this;
    }

    public function setSource(string $value)
    {
        $this->source = $value;
        return $this;
    }

    public function setType(string $value)
    {
        $this->type = $value;
        return $this;
    }

    public function setBatchNo(string $value)
    {
        $this->batch_no = $value;
        return $this;
    }

    public function setTitle(string $value)
    {
        $this->title = $value;
        return $this;
    }

    public function setDescription(string $value)
    {
        $this->description = $value;
        return $this;
    }

    public function setGateway(string $value)
    {
        $this->gateway = $value;
        return $this;
    }

    public function setActivity(string $value)
    {
        $this->activity = $value;
        return $this;
    }

    public function byGateway(): array
    {
        if ($this->gateway == PaymentConstants::GATEWAY_CATHOLICPAY) {
            return self::withCatholicPay();
        }

        throw new SubscriptionException("Gateway is currently inactve");
    }

    public function withCatholicPay(): array
    {
        DB::beginTransaction();
        try {
            $user = $this->user;
            $currency = $this->currency;
            $amount = $this->amount;

            $catholicpay_service = new CatholicPayService;
            $wallet = $catholicpay_service->getBalance();

            $this->transaction = $transaction = TransactionService::create([
                "user_id" => $user->id,
                "currency_id" => $currency->id,
                "amount" => $amount,
                "fees" => 0,
                "description" => $this->description,
                "activity" => TransactionActivityConstants::USER_SUBSCRIPTION_VIA_CATHOLICPAY,
                "category" => TransactionActivityConstants::USER_SUBSCRIPTION_VIA_CATHOLICPAY,
                "source" => $this->gateway,
                "action" => TransactionConstants::MONEY_SENT,
                "batch_no" => $this->batch_no ?? TransactionService::generateBatchNo(10),
                "sender_name" => $this->user->full_name,
                "receiver_name" => $this->gateway,
                "prev_balance" => $wallet["data"]["balance"] ?? 0,
                "current_balance" => $wallet["data"]["balance"] ?? 0,
                "type" => TransactionConstants::DEBIT,
                "status" => StatusConstants::PENDING
            ]);

            $metadata = [
                "user_id" => $user->id,
                "plan_id" => $this->plan->id,
                "transaction_id" => $this->transaction->id,
                "currency" => $currency->short_name,
                "amount" => $amount,
                "activity" => $transaction->activity,
            ];

            Payment::create([
                "user_id" => $user->id,
                "currency_id" => $currency->id,
                "reference" => $transaction->reference,
                "transaction_id" => $transaction->id,
                "amount" => $amount,
                "fees" => 0,
                "action" => PaymentConstants::PAYMENT,
                "metadata" => json_encode([
                    "payload" => $metadata ?? null
                ]),
                "activity" => PaymentConstants::SUBSCRIBE_TO_PLAN,
                "gateway" => $this->gateway
            ]);

            $initializeData = $catholicpay_service
                ->setCurrency($currency->short_name)
                ->setPaymentChannels(["card"])
                ->setCustomerData([
                    "name" => $this->user->full_name,
                    "email" => $user->email
                ])
                ->setMetadata($metadata)
                ->initialize($transaction->reference, $amount);

            if (blank($initializeData)) {
                throw new CatholicPayException("No response from CatholicPay");
            }

            if (($initializeData["status"] ?? null) != ApiConstants::GOOD_REQ_CODE) {
                throw new CatholicPayException($initializeData["message"] ?? "");
            }

            if (($initializeData["data"]["success"] ?? null) != true) {
                throw new CatholicPayException($initializeData["message"] ?? "");
            }

            DB::commit();
            $metadata["currency"] = $currency->minimal();
            return [
                "link" => $initializeData["data"]["data"]["payment_url"],
                "reference" => $initializeData["data"]["data"]["reference"],
                "currency" => $currency,
                "amount" => $amount,
                "transaction" => $transaction,
                "success" => true,
                "message" => $initializeData["data"]["message"],
                "metadata" => $metadata
            ];
        } catch (Exception $e) {
            DB::rollback();
            ExceptionService::broadcastOnAllChannels($e);
            throw new CatholicPayException("Couldn`t initiate transaction with CatholicPay");
        }
    }
}
