<?php

namespace App\Constants\Finance;

class PaymentConstants
{
    const GATEWAY_CATHOLICPAY = "CatholicPay";
    const GATEWAY_PAYSTACK = "Paystack";
    const GATEWAY_SQUAD = "Squad";
    const GATEWAY_FLUTTWAVE = "Flutterwave";
    const GATEWAY_STRIPE = "Stripe";
    const GATEWAY_MONNIFY = "Monnify";
    const PAY_WITH_CARD = "Card";
    const PAY_WITH_BANK = "Bank";
    const PAY_WITH_WALLET = "Wallet";
    const PAY_WITH_TRANSFER = "Transfer";

    const PAYMENT_OPTIONS = [
        self::PAY_WITH_WALLET => self::PAY_WITH_WALLET,
        // self::PAY_WITH_BANK,
        self::PAY_WITH_CARD => self::PAY_WITH_CARD
    ];


    const GATEWAYS = [
        self::GATEWAY_CATHOLICPAY => self::GATEWAY_CATHOLICPAY,
    ];

    const FUND_ACCOUNT_VIA_WEB = "FUND_ACCOUNT_VIA_WEB";
    const FUND_ACCOUNT_VIA_APP   = "FUND_ACCOUNT_VIA_APP";
    const SUBSCRIBE_TO_PLAN = "SUBSCRIBE_TO_PLAN";
    const SUBSCRIBE_TO_PLAN_WITH_BANK = "SUBSCRIBE_TO_PLAN_WITH_BANK";
    const FUND_WALLET_WITH_CARD = "FUND_WALLET_WITH_CARD";
    const TRANFER_FUND_WITH_PAYSTACK = "TRANFER_FUND_WITH_PAYSTACK";
    const PAYSTACK_SUPPORTED_CURRENCIES = ["USD", "NGN"];
    const SQUAD_SUPPORTED_CURRENCIES = ["USD", "NGN"];
    const CATHOLICPAY_SUPPORTED_CURRENCIES = ["USD", "NGN"];
    const MONNIFY_SUPPORTED_CURRENCIES = ["USD", "NGN"];
    const VOUCHER_REQUEST = "VOUCHER_REQUEST";
    const WEB_PAYMENT_FOR_UNIT = "WEB_PAYMENT_FOR_UNIT";
    const BANK_PROOF_PAYMENT_FOR_UNIT = "BANK_PROOF_PAYMENT_FOR_UNIT";

    const SINGLE = "Single";
    const MULTIPLE = "Multiple";

    const PAYGOLD = "Paygold";
    const WITHDRAWAL = "Withdrawal";
    const DEPOSIT = "Deposit";
    const PAYMENT = "Payment";

    const ME = "Me";
    const RECIPIENT = "Recipient";
    const SENDER = "Sender";
    const RECEIVER = "Receiver";

    const FEE_PAYMENT_OPTIONS = [
        self::ME => self::ME,
        self::RECIPIENT => self::RECIPIENT,
    ];

    const WEB_GATEWAYS = [
        self::GATEWAY_SQUAD => self::GATEWAY_SQUAD,
        self::GATEWAY_MONNIFY => self::GATEWAY_MONNIFY,
    ];

    const FEE_PAYMENT_OPTIONS_FOR_USERS = [
        self::RECEIVER => self::RECEIVER,
        self::SENDER => self::SENDER,
    ];

    const POS_PAYMENT_OPTIONS = [
        self::PAY_WITH_CARD => self::PAY_WITH_CARD,
        self::PAY_WITH_TRANSFER => self::PAY_WITH_TRANSFER,
    ];
}
