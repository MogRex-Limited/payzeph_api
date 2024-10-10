<?php

namespace App\Services\Auth\User;

use App\Constants\Finance\CurrencyConstants;
use App\Models\Currency;
use App\Models\User;
use App\Services\User\UserService;
use App\Services\Auth\User\UserVerifyService;
use App\Services\Finance\Wallet\WalletService;
use App\Services\Notification\AppMailerService;

class UserRegistrationService
{
    public $user_service;
    public $wallet_service;
    public $user_verify_service;

    public function __construct()
    {
        $this->user_service = new UserService;
        $this->user_verify_service = new UserVerifyService;
        $this->wallet_service = new WalletService;
    }

    public function create(array $data): User
    {
        $user = $this->user_service->create($data);
        $this->user_verify_service->sendPin($user);
        return $user;
    }

    public  function postRegisterActions(User $user)
    {
        // $this->sendWelcomeMessage($user);

        $currency = Currency::status()
            ->where("group", CurrencyConstants::TOKEN_GROUP)
            ->where("type", CurrencyConstants::USDC_TOKEN)
            ->first();

        // Create USDC Wallet
        $this->wallet_service->create([
            "user_id" => $user->id,
            "type" => CurrencyConstants::TOKEN_GROUP,
            "currency_id" => $currency?->id
        ]);

        $currency = Currency::status()
            ->where("group", CurrencyConstants::FIAT_GROUP)
            ->where("type", CurrencyConstants::DOLLAR_CURRENCY)
            ->first();

        $this->wallet_service->create([
            "user_id" => $user->id,
            "type" => CurrencyConstants::TOKEN_GROUP,
            "currency_id" => $currency?->id
        ]);
    }

    private function sendWelcomeMessage(User $user)
    {
        AppMailerService::send([
            "data" => [
                'user' => $user,
            ],
            "to" => $user->email,
            "template" => "emails.user.welcome",
            "subject" => "Welcome to Mentra",
        ]);
    }
}
