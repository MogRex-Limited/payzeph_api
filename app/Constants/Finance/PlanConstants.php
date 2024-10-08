<?php

namespace App\Constants\Finance;

class PlanConstants
{
    const DAILY = "Daily";
    const WEEKLY = "Weekly";
    const MONTHLY = "Monthly";
    const QUARTERLY = "Quarterly";
    const BI_ANNUALLY = "Bi Annually";
    const YEARLY = "Yearly";
    const FIXED = "Fixed";
    const PERCENTAGE = "Percentage";
    const ONE_TIME = "One-time";
    const RECURRENT = "Recurrent";

    const HITS = "HITS";

    const KEY_OPTIONS = [
        self::HITS => "Hits"
    ];

    const VALUE_TYPE_OPTIONS = [
        self::FIXED => "Fixed",
        self::PERCENTAGE => "Percentage",
    ];

    const SUBSCRIPTION_TYPE_OPTIONS = [
        self::ONE_TIME => "One-time",
        self::RECURRENT => "Recurrent",
    ];

    const FREQUENCY_OPTIONS = [
        self::MONTHLY => "Monthly",
        self::QUARTERLY => "Quarterly",
        self::BI_ANNUALLY => "Bi Annually",
        self::YEARLY => "Yearly",
    ];

    const FREE_PLAN = "Free Plan";
    const BASIC_PLAN = "Basic";
    const PRO_PLAN = "Pro";

    const KEY_SMS_RATE = "sms_rate";
    const KEY_TRANSACTION_FEE = "transaction_fee";

    const STATIC = "Static";
    const CUSTOM = "Custom";

    const TYPES_OPTIONS = [
        self::STATIC => self::STATIC,
        self::CUSTOM => self::CUSTOM,
    ];

    const BENEFIT_OPTIONS = [
        self::KEY_SMS_RATE => "SMS Rates",
        self::KEY_TRANSACTION_FEE => "Transaction Fee",
    ];

    const BENEFIT_DESCRIPTIONS = [
        self::KEY_SMS_RATE => "Enjoy sending sms at a flat rate of {{value}} upon subscription to this plan.",
        self::KEY_TRANSACTION_FEE => "Enjoy performing transaction a fixed fee of {{value}} upon subscription to this plan.",
    ];

    const SUB_FREQUENCY_OPTIONS = [
        self::DAILY => "Daily",
        self::WEEKLY => "Weekly",
        self::MONTHLY => "Monthly",
        self::YEARLY => "Yearly",
    ];
}
