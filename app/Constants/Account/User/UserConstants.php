<?php

namespace App\Constants\Account\User;

use App\Constants\General\StatusConstants;

class UserConstants
{
    const SUDO = "Sudo";
    const OWNER = "Owner";
    const USER = "User";
    const ADMIN = "Admin";
    const MEMBER = "Member";
    const AMBASSADOR = "Ambassador";
    const SUPER_ADMIN = "Super Admin";

    const PARISH = "Parish";
    const GROUP = "Group";
    const CHANNEL = "Channel";

    const GREEN = "Green";
    const YELLOW = "Yellow";
    const ORANGE = "Orange";
    const RED = "Red";

    const LEVEL_ONE = "Level 1";
    const LEVEL_TWO = "Level 2";
    const LEVEL_THREE = "Level 3";

    const UPDATE_VERIFICATION_STATUS = "UPDATE_VERIFICATION_STATUS";

    const ROLES = [
        self::OWNER,
        self::ADMIN,
        // self::MEMBER,
        self::USER
    ];

    const FLAGS = [
        self::GREEN => self::GREEN,
        self::YELLOW => self::YELLOW,
        self::ORANGE => self::ORANGE,
        self::RED => self::RED
    ];

    const NON_OWNER_ROLES = [
        self::ADMIN,
        self::MEMBER,
    ];

    const STATUSES = [
        StatusConstants::PENDING,
        StatusConstants::ACTIVE,
        StatusConstants::INACTIVE,
    ];

    const ADMIN_TYPES = [
        self::PARISH => self::PARISH,
        self::GROUP => self::GROUP,
    ];

    const ACCOUNT_LEVELS = [
        self::LEVEL_ONE => self::LEVEL_ONE,
        self::LEVEL_TWO => self::LEVEL_TWO,
        self::LEVEL_THREE => self::LEVEL_THREE,
    ];

    const ALL_ROLES = [
        self::ADMIN => self::ADMIN,
        self::PARISH => self::PARISH,
        self::GROUP => self::GROUP,
        self::CHANNEL => self::CHANNEL,
    ];

    const ANNOUNCEMENT_AUDIENCE = [
        self::USER => self::USER,
        // self::ADMIN => self::ADMIN,
    ];
}
