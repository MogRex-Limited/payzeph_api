<?php

namespace App\Services\Finance\Transaction;

use App\Constants\Finance\TransactionConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public static function getById($key, $column = "id"): Transaction
    {
        $transaction = Transaction::where($column, $key)->first();
        if (empty($transaction)) {
            throw new ModelNotFoundException("Transaction not found");
        }
        return $transaction;
    }

    public static function validate($data)
    {
        $validator = Validator::make($data, [
            "user_id" => "bail|required|exists:users,id",
            "currency_id" => "bail|required|exists:currencies,id",
            "wallet_id" => "bail|nullable|exists:wallets,id",
            "amount" => "bail|required|numeric|gt:0",
            "unit_quantity" => "bail|required|numeric|gt:0",
            "fees" => "bail|required|numeric|gt:-1",
            "description" => "bail|required|string",
            "activity" => "bail|required|string",
            "batch_no" => "bail|nullable|string",
            "reference" => "bail|nullable|string",
            "type" => "bail|required|string|" . Rule::in([
                TransactionConstants::CREDIT,
                TransactionConstants::DEBIT
            ]),
            "status" => "bail|required|string|" . Rule::in([
                StatusConstants::COMPLETED,
                StatusConstants::PENDING,
                StatusConstants::PROCESSING,
                StatusConstants::REFUNDED,
                StatusConstants::FAILED,
                StatusConstants::ROLLBACK,
                StatusConstants::DECLINED
            ]),
            "prev_balance" => "bail|required|numeric|gt:-1",
            "current_balance" => "bail|required|numeric|gt:-1",
            "metadata" => "bail|nullable|string",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public static function create($data): Transaction
    {
        $data = self::validate($data);
        $data["reference"] = $data["reference"] ?? self::generateReferenceNo();
        $transaction = Transaction::create($data);
        return $transaction;
    }

    public static function generateBatchNo($length = 10)
    {
        $key = "BN_" . MethodsHelper::generateRandomDigits($length);
        $check = Transaction::where("batch_no", $key)->count();
        if ($check > 0) {
            return self::generateBatchNo();
        }
        return $key;
    }

    public static function generateReferenceNo($length = 8)
    {
        $key = "RF-" . MethodsHelper::getRandomToken($length, true);
        $check = Transaction::where("reference", $key)->count();
        if ($check > 0) {
            return self::generateReferenceNo();
        }
        return $key;
    }

    public static function getByReference($reference): Transaction
    {
        $transaction = Transaction::where("reference", $reference)->first();

        if (empty($transaction)) {
            throw new ModelNotFoundException(
                "Transaction not found",
            );
        }
        return $transaction;
    }

    public static function oldWalletBalance($wallet)
    {
        return [
            "balance" => $wallet->balance,
            "credit" => $wallet->credit,
            "debit" => $wallet->credit,
            "locked_balance" => $wallet->locked_balance,
        ];
    }

    public static function newWalletBalance($wallet)
    {
        return [
            "balance" => $wallet->balance,
            "credit" => $wallet->credit,
            "debit" => $wallet->credit,
            "locked_balance" => $wallet->locked_balance,
        ];
    }
}
