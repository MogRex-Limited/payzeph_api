<?php

namespace Database\Seeders;

use App\Constants\Finance\CurrencyConstants;
use App\Constants\General\StatusConstants;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            [
                "name" => "Nigerian Naira",
                "group" => CurrencyConstants::CURRENCY_GROUP,
                "type" => CurrencyConstants::NAIRA_CURRENCY,
                "price_per_dollar" => 1500,
                "short_name" => "NGN",
                "logo" => "₦",
                "status" => StatusConstants::ACTIVE
            ],
            [
                "name" => "Us Dollar",
                "group" => CurrencyConstants::CURRENCY_GROUP,
                "type" => CurrencyConstants::DOLLAR_CURRENCY,
                "price_per_dollar" => 1,
                "short_name" => "USD",
                "logo" => "$",
                "status" => StatusConstants::ACTIVE
            ],
            [
                "name" => "Euro",
                "group" => CurrencyConstants::CURRENCY_GROUP,
                "type" => CurrencyConstants::EURO_CURRENCY,
                "price_per_dollar" =>  0.8,
                "short_name" => "EUR",
                "logo" => "€",
                "status" => StatusConstants::ACTIVE
            ],
            [
                "name" => "Pound",
                "group" => CurrencyConstants::CURRENCY_GROUP,
                "type" => CurrencyConstants::POUND_CURRENCY,
                "price_per_dollar" =>  0.5,
                "short_name" => "GBP",
                "logo" => "£",
                "status" => StatusConstants::ACTIVE
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(["type" => $currency["type"]], $currency);
        }
    }
}
