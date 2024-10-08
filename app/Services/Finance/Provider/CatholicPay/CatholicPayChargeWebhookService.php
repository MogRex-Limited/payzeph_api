<?php

namespace App\Services\Finance\Provider\CatholicPay;

use App\Constants\Finance\TransactionActivityConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\Finance\Payment\CatholicPayException;
use App\Exceptions\Finance\Transaction\TransactionException;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\Subscription\NewSubscriptionNotification;
use App\Services\Finance\Plan\PlanService;
use App\Services\Finance\Subscription\SubscriptionService;
use Illuminate\Support\Facades\Notification;

class CatholicPayChargeWebhookService
{
    public array $payload;
    public $event;
    public Currency $currency;
    public User $user;
    public Transaction $transaction;
    public $model;
    public $card_token;

    public function setPayload(array $value)
    {
        $this->payload = $value;
        return $this;
    }

    public function handle()
    {
        $this->parsePayload();
        $this->actionHandler();
    }

    public function parsePayload()
    {
        $payload = $this->payload["data"];

        if (empty($payload)) {
            throw new CatholicPayException("Charge data not set!");
        }

        $this->transaction = $this->verifyTransactionStatus($payload);
        // $this->currency = $this->setCurrency($payload);
        $this->user = $this->setUser();
        $this->model = $this->transaction->modelObject;
    }

    private function verifyTransactionStatus($payload)
    {
        $transaction = Transaction::where("reference", $payload["reference"])->first();

        if (empty($transaction)) {
            throw new TransactionException("Transaction does not exist on our record");
        }

        if (!empty($transaction) && ($transaction->status == StatusConstants::COMPLETED)) {
            throw new TransactionException("You cannot process a completed transaction");
        }

        return $transaction;
    }

    public function setCurrency($payload)
    {
        $short_name = $payload["currency"];
        $currency = Currency::where("short_name", $short_name)
            ->first();

        return $this->currency = $currency;
    }

    public function setUser()
    {
        return $this->user = $this->transaction->user;
    }

    private function actionHandler()
    {
        if ($this->payload["data"]["status"] == StatusConstants::COMPLETED) {
            return $this->handleSuccess($this->payload);
        }
    }

    private function handleSuccess($payload)
    {
        if ($this->transaction->activity == TransactionActivityConstants::PARISH_SUBSCRIPTION_VIA_CATHOLICPAY) {
            $this->completedMerchantSubscription();
        }
    }

    public function completedMerchantSubscription()
    {
        $transaction = $this->transaction;

        $metadata = json_decode($transaction->payment->metadata, true);
        $plan = PlanService::getById($metadata["payload"]["plan_id"]);

        $transaction->update([
            "completed_at" => now(),
            "status" => StatusConstants::COMPLETED
        ]);

        $transaction->payment()->update([
            "status" => StatusConstants::COMPLETED
        ]);


        $subscription = SubscriptionService::subscribeToPlan($this->model, $plan);
        Notification::send($this->model, (new NewSubscriptionNotification($subscription)));
    }
}
