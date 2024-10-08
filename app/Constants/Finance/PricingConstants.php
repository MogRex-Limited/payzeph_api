<?php

namespace App\Constants\Finance;

class PricingConstants
{
    const MTN = "MTN";
    const GLO = "Glo";
    const AIRTEL = "Airtel";
    const NINE_MOBILE = "9Mobile";

    const UNIT_PRICING_OPTIONS = [
        self::MTN => "2",
        self::GLO => "1.5",
        self::AIRTEL => "2",
        self::NINE_MOBILE => "1.3",
    ];
}
