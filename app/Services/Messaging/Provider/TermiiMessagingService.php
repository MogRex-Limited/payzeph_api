<?php

namespace App\Services\Messaging\Provider;

use App\Constants\Finance\PricingConstants;
use App\Constants\Finance\TransactionActivityConstants;
use App\Constants\Finance\TransactionConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ProviderException;
use App\Exceptions\Messaging\MessagingException;
use App\Helpers\MethodsHelper;
use App\Models\Currency;
use App\Services\External\TermiiService;
use App\Services\Finance\Pricing\PricingService;
use App\Services\Finance\Transaction\TransactionService;
use App\Services\Finance\Wallet\WalletService;
use App\Services\Messaging\MessageService;
use App\Services\System\ExceptionService;
use Exception;
use Illuminate\Support\Facades\DB;

class TermiiMessagingService extends MessageService
{
    public $termii_service;
    public $validated_data;
    public $message;
    public $user;

    public function __construct($user)
    {
        parent::__construct($user);
    }

    public function init(array $data)
    {
        $this->message = $data["message"];
        $this->termii_service = new TermiiService;
        $this->user = $data["user"];
        $this->validated_data = $data["base_payload"];
        // $this->parsePayload();
        $this->process();
    }

    public function process()
    {
        try {
            $this->chargeUser();

            $response = $this->termii_service->messagingData([
                "to" => $this->validated_data["recipient"],
                "from" => $this->message?->sender,
                "sms" => $this->message?->content ?? $this->validated_data["content"],
                "type" => "plain",
                "channel" => "generic",
            ])->sendSingleMessage();

            if ($response["code"] !== "ok") {
                throw new MessagingException("Failed to send message. Please try again");
            }

            $this->message->update([
                "status" => StatusConstants::SUCCESSFUL
            ]);
        } catch (ProviderException | MessagingException $th) {
            ExceptionService::broadcastOnAllChannels($th);
            throw new MessagingException("Your message is currently being processed");
        } catch (Exception $e) {
            ExceptionService::broadcastOnAllChannels($e);
            throw $e;
        }
    }

    public function chargeUser()
    {
        DB::beginTransaction();
        try {
            $wallet = WalletService::getByUserId($this->user->id);
            $unit_amount = $this->calculateUnit($this->validated_data["recipient"]);

            if (empty($unit_amount)) {
                throw new InvalidRequestException("Unable to determine the network provider of the number");
            }

            $check = PricingService::calculate([
                "quantity" => $unit_amount,
                "type" => "unit"
            ]);

            $amount = $check["price"] ?? null;

            $old_wallet_balance = TransactionService::oldWalletBalance($wallet);
            WalletService::debit($wallet, $unit_amount);
            $new_wallet_balance = TransactionService::newWalletBalance($wallet->refresh());
            $currency = Currency::first();

            TransactionService::create([
                "user_id" => $this->user->id,
                "wallet_id" => $wallet->id,
                "currency_id" => $currency->id,
                "amount" => $amount,
                "fees" => 0,
                "description" => "Sending of SMS",
                "unit_quantity" => $unit_amount,
                "reference" => TransactionService::generateReferenceNo(),
                "activity" => TransactionActivityConstants::SENDING_OF_SMS,
                "batch_no" => TransactionService::generateBatchNo(),
                "type" => TransactionConstants::DEBIT,
                "status" => StatusConstants::COMPLETED,
                "action" => TransactionConstants::SMS_SENT,
                "prev_balance" => $old_wallet_balance["balance"],
                "current_balance" => $new_wallet_balance["balance"],
                "metadata" => json_encode(array_merge($this->validated_payload["metadata"] ?? [], [
                    "wallet" => [
                        "old" => $old_wallet_balance,
                        "new" => $new_wallet_balance
                    ]
                ])),
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function calculateUnit($mobile_number)
    {
        $clean_number = MethodsHelper::removeCountryCode($mobile_number);
        $network = getNetworkByPrefix($clean_number);
        $pricing = PricingConstants::UNIT_PRICING_OPTIONS[$network] ?? null;
        return $pricing;
    }
}
