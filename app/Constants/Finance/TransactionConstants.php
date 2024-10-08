<?php

namespace App\Constants\Finance;

use App\Constants\General\StatusConstants;

class TransactionConstants
{

    const DEBIT = "Debit";
    const CREDIT = "Credit";

    const FIXED_VALUE = "Fixed";
    const CUSTOM_VALUE = "Custom";
    const PERCENTAGE_VALUE = "Percentage";

    const MANUAL_DEPOSIT_FIXED_FEE = 100;

    const MONEY_SENT = "Money sent";
    const MONEY_RECEIVED = "Money received";
    const BANK_DEPOSIT = "Bank deposit";
    const BANK_TRANSFER = "Bank transfer";
    const WEB_PAYMENT_FOR_UNIT = "WEB_PAYMENT_FOR_UNIT";
    const SMS_SENT = "SMS sent";

    const TRANSACTION_OPTIONS = [
        StatusConstants::PENDING => "Pending",
        StatusConstants::COMPLETED => "Completed",
        StatusConstants::DECLINED => "Declined",
        StatusConstants::FAILED => "Failed",
    ];

    const TYPE_OPTIONS = [
        self::CREDIT => self::CREDIT,
        self::DEBIT => self::DEBIT
    ];
}
