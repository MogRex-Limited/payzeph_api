<?php

namespace App\Services\Finance;


use App\Constants\Finance\TransactionConstants;
use App\Constants\Finance\WalletConstants;
use App\Models\Client;
use App\Models\Talent;
use App\Models\Transaction;
use App\Notifications\Finance\NewTransactionNotification;
use DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Validator;

class TransactionService
{
    public $send_notification = true;

    function notify(bool $value)
    {
        $this->send_notification = $value;
        return $this;
    }

    public static function generateCode()
    {
        $code = 'TRX-' . getRandomToken(8, true);
        $check = Transaction::where('code', $code)->withTrashed()->count();
        if ($check > 0) {
            return self::generateCode();
        }

        return $code;
    }

    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $validator = Validator::make($data, [
                'user_id' => 'required|numeric',
                'user_type' => 'required|' . Rule::in([Client::class, Talent::class]),
                'currency' => 'required|' . Rule::in(array_keys(WalletConstants::CURRENCIES)),
                'type' => 'required|string|' . Rule::in(array_keys(TransactionConstants::TYPES)),
                'amount' => 'required|numeric',
                'fee' => 'nullable|numeric',
                'status' => 'nullable|string',
                'description' => 'nullable|string',
                'external_reference' => 'nullable|string',
                'related_model' => 'nullable',
                'request_id' => 'nullable',
                'created_by' => 'nullable',
                'milestone_id' => 'nullable',
                'gig_id' => 'nullable',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();
            $data["code"] = self::generateCode();
            $data["status"] = $data["status"] ?? TransactionConstants::STATUS_COMPLETED;

            if (!empty($model = $data["related_model"] ?? null)) {
                $data["related_model_id"] = $model->id;
                $data["related_model_type"] = get_class($model);
                unset($data["related_model"]);
            }

            $transaction = Transaction::create($data);

            if ($this->send_notification) {
                Notification::send($transaction->user, new NewTransactionNotification($transaction));
            }

            return $transaction;
        });
    }

    public function markAsComplete(Transaction $transaction): Transaction
    {
        $transaction->update([
            "status" => TransactionConstants::STATUS_COMPLETED
        ]);
        if ($this->send_notification) {
            Notification::send($transaction->user, new NewTransactionNotification($transaction));
        }

        return $transaction->refresh();
    }

    function list(Client|Talent $user, array $query)
    {
        $builder = Transaction::where([
            "user_id" => $user->id,
            "user_type" => get_class($user),
        ]);

        if (!empty($key = $query[""] ?? null)) {

        }

        return $builder;
    }

}
