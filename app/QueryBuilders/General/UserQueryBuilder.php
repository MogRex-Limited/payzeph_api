<?php

namespace App\QueryBuilders\General;

use App\Models\User;
use Illuminate\Http\Request;

class UserQueryBuilder
{
    public static function filterList(Request $request)
    {
        $builder = new User();

        if (!empty($key = $request->search)) {
            $builder = $builder->search($key);
        }

        return $builder;
    }
}
