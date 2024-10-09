<?php

namespace App\Services\Finance\Wallet;

use App\Constants\General\StatusConstants;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use App\Exceptions\General\InvalidRequestException;
use App\Exceptions\General\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WalletService
{

    const INSUFFICIENT_COINS_ERROR = 3001;
    const INSUFFICIENT_FUNDS = 30001;
    const WALLET_NOT_FOUND = 3404;

    public static function getByUserId($user_id)
    {
        $wallet = Wallet::where("user_id", $user_id)->first();
        if (empty($wallet)) {
            $wallet = self::create([
                "user_id" => $user_id,
                "status" => StatusConstants::ACTIVE
            ]);
        }
        return $wallet;
    }

   

    public static function debit(Wallet $wallet, float $amount)
    {
        $wallet->refresh();
        if ($wallet->balance < $amount) {
            throw new InvalidRequestException(
                "You don`t have enough unit(s) for this transaction!",
                self::INSUFFICIENT_FUNDS
            );
        }
        $wallet->update([
            "balance" => $wallet->balance - $amount,
            "total_debit" => $wallet->total_debit + $amount
        ]);
    }


    public static function credit(Wallet $wallet, float $amount)
    {
        $wallet->refresh();
        $wallet->update([
            "balance" => $wallet->balance + $amount,
            "total_credit" => $wallet->total_credit + $amount
        ]);

        return $wallet->refresh();
    }
}
