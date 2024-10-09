<?php

namespace App\Services\Finance\Currency;

use App\Constants\Finance\CurrencyConstants;
use App\Constants\Media\FileConstants;
use App\Constants\StatusConstants;
use App\Exceptions\Finance\Wallet\WalletException;
use App\Models\Currency;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CurrencyService
{

    const CURRENCY_NOT_FOUND = 3404;

    public static function getByType($type): Currency
    {
        $currency = Currency::where("type", $type)->first();

        if (empty($currency)) {
            // throw new WalletException(
            //     "Currency not found",
            //     self::CURRENCY_NOT_FOUND
            // );
            $currency = Currency::firstOrCreate([
                "name" => "Nigerian Naira",
                "group" => CurrencyConstants::CURRENCY_GROUP,
                "type" => CurrencyConstants::NAIRA_CURRENCY,
                "price_per_dollar" => 700,
                "short_name" => "NGN",
            ], [    
                "logo" => "/",
                "status" => StatusConstants::ACTIVE
            ]);
        }
        return $currency;
    }

    public static function getById($key, $column = "id"): Currency
    {
        $currency = Currency::where($column, $key)->first();

        if (empty($currency)) {
            throw new WalletException(
                "Currency not found",
                self::CURRENCY_NOT_FOUND
            );
        }
        return $currency;
    }

    public static function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "status" => "required|string|" . Rule::in(StatusConstants::ACTIVE_OPTIONS),
            "name" => 'required|string',
            "type" => "required|string|unique:currencies,type,$id",
            "price_per_dollar" => 'required|numeric|gt:-1',
            "short_name" => "required|string|unique:currencies,short_name,$id",
            "logo" => "image|" . Rule::requiredIf(empty($id)),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, $id = null)
    {
        $data = $this->validate($data, $id);

        if (!empty($file = $data["logo"] ?? null)) {
            $data["logo"] = putFileInPrivateStorage($file, FileConstants::CURRENCY_LOGO_PATH);
        }

        if (!empty($id)) {
            $currency = Currency::find($id);
            $old_logo = $currency->logo;
            $currency->update($data);
            if (!empty($old_logo) && !empty($data["logo"] ?? null)) {
                deleteFileFromPrivateStorage($old_logo);
            }
        } else {
            $currency =  Currency::create($data);
        }

        return $currency;
    }
}
