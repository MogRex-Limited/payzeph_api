<?php

namespace App\QueryBuilders\Finance;

use App\Constants\Finance\WalletConstants;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletQueryBuilder
{
    public static function filterList(Request $request)
    {
        $builder =  Wallet::with(["currency", "user", "parish", "group", "channel"])
            ->whereNotIn('id', function ($query) {
                $query->select('id')
                    ->from('wallets')
                    ->whereIn('type', [WalletConstants::SAVINGS, WalletConstants::AWARDS])
                    ->whereNull('sub_type');
            });

        if (!empty($key = $request->type)) {
            if (!in_array($key, WalletConstants::SAVINGS_WALLET_OPTIONS)) {
                $builder = $builder->where('type', $key);
            } else {
                $builder = $builder->where('sub_type', $key);
            }
        }

        if (!empty($key = $request->currency_id)) {
            $builder = $builder->where('currency_id', $key);
        }

        if (!empty($key = $request->user_id)) {
            $builder = $builder->where('user_id', $key);
        }

        if (!empty($key = $request->search)) {
            $builder = $builder->search($key);
        }

        if (!empty($key = $request->status)) {
            $builder = $builder->where("status", $key);
        }

        return $builder;
    }
}
