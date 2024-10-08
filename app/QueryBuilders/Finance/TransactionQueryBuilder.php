<?php

namespace App\QueryBuilders\Finance;

use App\Models\Transaction;
use Illuminate\Http\Request;
use PDO;

class TransactionQueryBuilder
{
    public static function filterList(Request $request)
    {
        $builder = Transaction::with(["currency", "user"]);

        if (!empty($key = $request->type)) {
            $builder = $builder->where('type', $key);
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

        if (!empty($start = $request->start_date) && !empty($end = $request->end_date)) {
            $builder = $builder->whereBetween('created_at', [$start, $end]);
        }

        return $builder;
    }
}
