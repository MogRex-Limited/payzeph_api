<?php

namespace App\Constants\Auth;

class PinConstants
{
    const SESSION_KEY = 'session_otp_verified';

    const TYPE_LOGIN = 'login';

    const TYPE_ACCOUNT_SETUP = 'account_setup';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_VERIFY_EMAIL = 'verify_email';
    const TYPE_UPDATE_PARISHIONER_PROFILE = "update_parishioner_profile";
    const TYPE_VIEW_PARISHIONER_PROFILE = "view_parishioner_profile";

    const TITLES = [
        self::TYPE_PASSWORD_RESET => "Password Reset",
        self::TYPE_VERIFY_EMAIL => "Verify Email",
        self::TYPE_UPDATE_PARISHIONER_PROFILE => "Update Parishioner Profile",
        self::TYPE_VIEW_PARISHIONER_PROFILE => "View Parishioner Profile",
    ];

    const PASSWORD_REGEX = "^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,32}$";
}
