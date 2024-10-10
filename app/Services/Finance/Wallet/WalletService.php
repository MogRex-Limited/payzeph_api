<?php

namespace App\Services\Finance\Wallet;

use App\Constants\Finance\TransactionConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\Finance\Wallet\WalletException;
use App\Exceptions\General\ModelNotFoundException;
use App\Jobs\Finance\CreditWalletJob;
use App\Jobs\Finance\DebitWalletJob;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Finance\Currency\CurrencyService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public $transaction_data = [];

    public Transaction $transaction;

    public User $user;

    public $currency;

    function setCurrency(string $currency_id)
    {
        $this->currency = CurrencyService::getById($currency_id);
        return $this;
    }

    function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    function withTransaction(array $data)
    {
        $this->transaction_data = $data;
        return $this;
    }

    private function validate(array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => 'nullable',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'nullable|numeric',
            'action' => 'nullable|string|' . Rule::in(array_keys(TransactionConstants::TYPE_OPTIONS)),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
    }

    public function credit(float $amount)
    {
        return DB::transaction(function () use ($amount) {

            $data = [
                'user_id' => $this->user?->id,
                'currency_id' => $this->currency->id,
                'amount' => $amount,
                'action' => TransactionConstants::CREDIT,
            ];

            $this->validate($data);

            $wallet = $this->wallet();

            if (!empty($this->transaction_data)) {
                $this->transaction = (new TransactionService)->create(array_merge([
                    'user_id' => $this->user?->id,
                    'type' => $data['action'],
                    'currency_id' => $data['currency_id'],
                    'amount' => $data['amount']
                ], $this->transaction_data));
            }

            if (isset($this->transaction) && $this->transaction->status != StatusConstants::COMPLETED) {
                return;
            }

            $wallet->update([
                "balance" => $wallet->balance + $amount,
                'total_credit' => $wallet->total_credit + $amount,
            ]);
        });
    }

    public function debit(float $amount)
    {
        return DB::transaction(function () use ($amount) {

            $data = [
                'user_id' => $this->user?->id,
                'currency' => $this->currency,
                'amount' => $amount,
                'action' => TransactionConstants::DEBIT,
            ];

            $this->validate($data);

            $wallet = $this->wallet();

            $new_balance = $wallet->balance - $amount;

            if ($new_balance < 0) {
                throw new WalletException("Insufficient funds");
            }

            if (!empty($this->transaction_data)) {
                $this->transaction = (new TransactionService)->create(array_merge([
                    'user_id' => $this->user?->id,
                    'type' => $data['action'],
                    'currency_id' => $data['currency_id'],
                    'amount' => $data['amount']
                ], $this->transaction_data));
            }

            if (isset($this->transaction) && $this->transaction->status != StatusConstants::COMPLETED) {
                return;
            }

            $wallet->update([
                "balance" => $new_balance,
                'total_debit' => $wallet->total_debit + $amount,
            ]);
        });
    }

    public function wallet(): Wallet
    {
        $wallet = Wallet::where([
            'user_id' => $this->user?->id,
            'currency_id' => $this->currency->id,
        ])->first();

        if (empty($wallet)) {
            $wallet = Wallet::create([
                'user_id' => $this->user?->id,
                'currency_id' => $this->currency->id,
                'balance' => 0,
            ]);
        }
        return $wallet;
    }

    public function dispatchCreditJob(float $amount)
    {
        CreditWalletJob::dispatch($this, $amount)->onQueue('wallet-transactions');
    }

    public function dispatchDebitJob(float $amount)
    {
        $wallet = $this->wallet();

        if ($wallet->balance < $amount) {
            throw new WalletException("Insufficient funds");
        }

        DebitWalletJob::dispatch($this, $amount)->onQueue('wallet-transactions');
    }

    public function wallets(): Collection
    {
        $wallets = Wallet::where([
            'user_id' => $this->user?->id,
        ])->get();
        return $wallets;
    }

    public static function getById($id): Wallet
    {
        $wallet = Wallet::where("id", $id)->first();

        if (empty($wallet)) {
            throw new ModelNotFoundException("Wallet not found");
        }

        return $wallet;
    }

    public static function create(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($data, [
                "user_id" => "required|exists:users,id",
                'currency_id' => 'required|exists:currencies,id',
                "type" => "required|string",
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            
            $data =  $validator->validated();
            $data["number"] = generateRandomDigits(10);
            $wallet = Wallet::create($data);
            DB::commit();
            return $wallet;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
