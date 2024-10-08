<?php

namespace App\Services\Finance\Payment\Bank;

use App\Constants\Finance\PaymentConstants;
use App\Constants\Finance\TransactionActivityConstants;
use App\Constants\Finance\TransactionConstants;
use App\Constants\General\StatusConstants;
use App\Constants\Media\FileConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\MethodsHelper;
use App\Models\BankPaymentProof;
use App\Models\Payment;
use App\Notifications\Payment\BankPaymentProofDeclined;
use App\Notifications\Payment\UnitPurchaseNotification;
use App\Services\Finance\Pricing\PricingService;
use App\Services\Finance\Transaction\TransactionService;
use App\Services\Finance\Wallet\WalletService;
use App\Services\Media\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BankPaymentService
{
    protected $file_service;
    protected $file;

    public function __construct()
    {
        $this->file_service = new FileService;
    }

    public static function getById($id): BankPaymentProof
    {
        $proof = BankPaymentProof::find($id);
        if (empty($proof)) {
            throw new ModelNotFoundException("Proof not found");
        }
        return $proof;
    }

    public static function list()
    {
        $bank_payment_proofs = BankPaymentProof::with("user");
        return $bank_payment_proofs;
    }
    
    public static function validateProofUpload(array $data)
    {
        $validator = Validator::make($data, [
            "proof" => "required|file",
            "amount" => "required|numeric|gt:-1",
            "description" => "nullable|string",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        return $data;
    }

    public function submitProof(array $data)
    {
        DB::beginTransaction();
        try {
            $data = self::validateProofUpload($data);
            $this->file = $this->file_service->saveFromFile($data["proof"], FileConstants::BANK_PAYMENT_PROOF, null, auth()->id());

            BankPaymentProof::create(array_merge([
                "user_id"  => auth()->id(),
                "file_id"  => $this->file->id,
                "type" => "unit_purchase",
                "reference" => $this->generateReferenceNo(),
                "amount" => $data["amount"] ?? null,
                "description" => $data["description"] ?? null,
            ]));

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->file->cleanDelete();
            throw $th;
        }
    }

    public static function validateProofUpdate(array $data)
    {
        $validator = Validator::make($data, [
            "proof_id" => "required|exists:bank_payment_proofs,id",
            "status" => "required|string|" . Rule::in(array_keys(StatusConstants::BANK_PAYMENT_STATUS_OPTIONS)),
            "reason" => "nullable|string|" . Rule::requiredIf($data["status"] == StatusConstants::DECLINED)
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        return $data;
    }


    public function update(array $data)
    {
        DB::beginTransaction();
        try {
            $data = self::validateProofUpdate($data);
            $proof = self::getById($data["id"]);

            $proof->update([
                "status" => $data["status"],
            ]);

            if ($data["status"] == StatusConstants::APPROVED) {
                self::markAsApproved($proof);
            }
            if ($data["status"] == StatusConstants::DECLINED) {
                self::markAsDeclined($proof, $data["reason"]);
            }

            DB::commit();
            return $proof->refresh();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }

    public static function markAsApproved($proof)
    {
        $user = $proof->user;
        $amount = $proof->amount;

        $wallet = WalletService::getByUserId($proof->user_id);
        $old_wallet_balance = TransactionService::oldWalletBalance($wallet);
        $unit = self::calculateUnit($amount);
        $wallet = WalletService::credit($wallet, $unit);
        $new_wallet_balance = TransactionService::newWalletBalance($wallet);

        $transaction = TransactionService::create(array_merge([
            "user_id" => $user->id,
            "wallet_id" => $wallet->id,
            "currency_id" => $wallet->currency_id,
            "amount" => $amount,
            "fees" => 0,
            "description" => "Bank Payment for Unit",
            "unit_quantity" => $unit,
            "reference" => TransactionService::generateReferenceNo(),
            "activity" => TransactionActivityConstants::PAYMENT_WITH_BANK_PROOF,
            "batch_no" => TransactionService::generateBatchNo(),
            "type" => TransactionConstants::DEBIT,
            "status" => StatusConstants::COMPLETED,
            "action" => TransactionConstants::MONEY_SENT,
            "prev_wallet_balance" => $old_wallet_balance["balance"],
            "new_wallet_balance" => $new_wallet_balance["balance"],
            "metadata" => json_encode([
                "wallet" => [
                    "old" => $old_wallet_balance,
                    "new" => $new_wallet_balance
                ]
            ]),
        ]));

        Payment::create([
            "user_id" => $user->id,
            "currency_id" => $wallet->currency->id,
            "reference" => $transaction->reference,
            "transaction_id" => $transaction->id,
            "amount" => $amount,
            "fee" => $transaction->fees,
            "action" => PaymentConstants::PAYMENT,
            "metadata" => $transaction->metadata,
            "activity" => PaymentConstants::BANK_PROOF_PAYMENT_FOR_UNIT,
        ]);

        $proof->update([
            "approved_at" => now(),
            "approved_by" => auth("admin")->id(),
            "status" => StatusConstants::APPROVED,
        ]);

        Notification::send($user, new UnitPurchaseNotification($transaction));

        return $proof->refresh();
    }

    public static function markAsDeclined($proof, $reason)
    {
        $user = $proof->user;
        $proof->update([
            "status" => StatusConstants::DECLINED,
        ]);

        Notification::send($user, new BankPaymentProofDeclined($proof, $reason));
    }

    public static function calculateUnit($amount)
    {
        $pricing = (new PricingService)->calculate([
            "quantity" => $amount,
            "unit" => "money"
        ]);

        $unit = $pricing["unit"];
        return $unit;
    }

    public static function generateReferenceNo($length = 8)
    {
        $key = "RF-" . MethodsHelper::getRandomToken($length, true);
        $check = BankPaymentProof::where("reference", $key)->count();
        if ($check > 0) {
            return self::generateReferenceNo();
        }
        return $key;
    }
}
