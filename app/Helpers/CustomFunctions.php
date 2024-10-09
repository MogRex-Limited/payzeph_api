<?php

use App\Constants\Finance\PricingConstants;
use App\Constants\General\AppConstants;
use App\Constants\System\ModelConstants;
use App\Helpers\MethodsHelper;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

function pillClasses($value)
{
    return AppConstants::PILL_CLASSES[$value] ?? "primary";
}

function convertJsonStringToText($string)
{
    return MethodsHelper::convertJsonStringToText($string);
}
function str_limit($string, $limit = 20, $end  = '...')
{
    return MethodsHelper::str_limit(strip_tags($string), $limit, $end);
}

function slugify($value)
{
    return Str::slug($value);
}

function generateRandomDigits($length, $string = false)
{
    $min = (int) pow(10, $length - 1);
    $max = (int) (pow(10, $length) - 1);

    $digits = random_int($min, $max);

    if ($string == true) {
        return (string)$digits;
    } else {
        return (int)$digits;
    }
}

function encrypt_decrypt($action, $string)
{
    try {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = 'Hg99JHShjdfhjhejkse@14447DP';
        $secret_iv = 'T0EHVn0dUIK888JSBGDD';

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } elseif ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    } catch (\Exception $e) {
        return false;
    }
}


function modelKey(Model $model)
{
    return ModelConstants::parseModelKey($model);
}

/**Returns formatted money value
 * @param float amount
 * @param int places
 * @param string symbol
 */
function format_money($amount, $places = 2, $symbol = '₦')
{
    return $symbol  . '' . int_format((float)$amount, $places);
}

function int_format($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
{
    return MethodsHelper::int_format($number, $decimals, $decPoint, $thousandsSep);
}

function globalSetting()
{
    return Setting::latest()->first();
}

function networkPrefix()
{
    $mobile_networks = [
        PricingConstants::MTN => [
            '0803',
            '0816',
            '0903',
            '0810',
            '0806',
            '0703',
            '0706',
            '0813',
            '0814',
            '0906'
        ],
        PricingConstants::GLO => [
            '0805',
            '0905',
            '0807',
            '0811',
            '0705',
            '0815'
        ],
        PricingConstants::NINE_MOBILE => [
            '0909',
            '0908',
            '0818',
            '0809',
            '0817'
        ],
        PricingConstants::AIRTEL => [
            '0907',
            '0708',
            '0802',
            '0902',
            '0812',
            '0808',
            '0701'
        ],
        'Starcomms' => [
            '07028',
            '07029',
            '0819'
        ],
        'Visafone' => [
            '07025',
            '07026',
            '0704'
        ],
        'Multi-Links' => [
            '07027',
            '0709'
        ],
        'ZoomMobile' => [
            '0707'
        ],
        'NTEL' => [
            '0804'
        ],
        'Smile' => [
            '0702'
        ]
    ];

    return $mobile_networks;
}

function getNetworkByPrefix($phone_number)
{
    $mobile_networks = networkPrefix();

    // Extract the first 4 and 5 digits
    $prefix4 = substr($phone_number, 0, 4);
    $prefix5 = substr($phone_number, 0, 5);

    // Loop through each network and check if the prefix matches
    foreach ($mobile_networks as $network => $prefixes) {
        if (in_array($prefix4, $prefixes) || in_array($prefix5, $prefixes)) {
            return $network;
        }
    }

    return null;
}
