<?php

namespace App\Constants\Finance;


class TransactionCategoryConstants
{
    const TRANSACTION_CATEGORY_OPTIONS = [
        TransactionActivityConstants::PARISH_SUBSCRIPTION_VIA_CATHOLICPAY => "Subscription - Parish",
    ];

    const CATEGORY_ACTIVITIES = [
        TransactionActivityConstants::PARISH_SUBSCRIPTION_VIA_CATHOLICPAY => [
            TransactionActivityConstants::PARISH_SUBSCRIPTION_VIA_CATHOLICPAY,
        ]
    ];
}
