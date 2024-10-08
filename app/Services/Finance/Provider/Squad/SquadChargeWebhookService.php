<?php

namespace App\Services\Finance\Provider\Squad;

use App\Constants\Finance\TransactionActivityConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\Finance\Payment\CatholicPayException;
use App\Exceptions\Finance\Transaction\TransactionException;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\Payment\UnitPurchaseNotification;
use App\Services\Finance\Transaction\TransactionService;
use App\Services\Finance\Wallet\WalletService;
use Illuminate\Support\Facades\Notification;

class SquadChargeWebhookService
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
        if ($this->transaction->activity == TransactionActivityConstants::PAYMENT_WITH_SQUAD_VIA_WEB) {
            $this->completeUserPurchase($payload);
        }
    }

    public function completeUserPurchase($payload)
    {
        $transaction = $this->transaction;
        $wallet = WalletService::getByUserId($this->transaction->user_id);

        $old_wallet_balance = TransactionService::oldWalletBalance($wallet);
        $wallet = WalletService::credit($wallet, $transaction->unit_amount);
        $new_wallet_balance = TransactionService::newWalletBalance($wallet->refresh());

        $transaction->update([
            "completed_at" => now(),
            "status" => StatusConstants::COMPLETED,
            "prev_balance" => $old_wallet_balance["balance"],
            "current_balance" => $new_wallet_balance["balance"],
            "logs" => json_encode([
                "merchant_amount" => $payload["merchant_amount"],
                "gateway_ref" => $payload["gateway_ref"],
                "transaction_indicator" => $payload["transaction_indicator"],
                "merchant_id" => $payload["merchant_id"],
                "customer_mobile" => $payload["customer_mobile"],
                "meta" => $payload["meta"],
                "payment_information" => $payload["payment_information"]
            ]),
            "metadata" => json_encode([
                "wallet" => [
                    "old" => $old_wallet_balance,
                    "new" => $new_wallet_balance
                ]
            ])
        ]);

        $transaction->payment()->update([
            "status" => StatusConstants::COMPLETED
        ]);


        Notification::send($this->user, new UnitPurchaseNotification($transaction));
    }
}
