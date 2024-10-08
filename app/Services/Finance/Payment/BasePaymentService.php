<?php

namespace App\Services\Finance\Payment;

use App\Constants\Finance\PricingConstants;
use App\Models\Wallet;
use App\Services\Finance\Wallet\WalletService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BasePaymentService
{
    public $user;
    public $description;
    public $validated_payload;
    public Wallet $wallet;
    public $model;

    public function __construct($user)
    {
        $this->user = $user;
        $this->wallet = WalletService::getByUserId($this->user->id);
    }

    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            "amount" => "required|numeric|gt:0",
            "metadata" => "nullable|array",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated_data = $validator->validated();

        $this->validated_payload = $validated_data;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
}
