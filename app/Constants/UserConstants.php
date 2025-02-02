<?php

namespace App\Constants;

class UserConstants {
    public const ROLE_TYPE_ADMIN = 1;
    public const ROLE_TYPE_ATTENDEE = 2;
    public const ROLE_TYPE = [
        self::ROLE_TYPE_ADMIN => 'Admin',
        self::ROLE_TYPE_ATTENDEE => 'Attendee',
    ];

    public const STATUS_ACTIVE = 1;

    public const REGISTERED_USER = 1;
    public const GUEST_USER = 2;
    public const USER_TYPE = [
        self::REGISTERED_USER => 'Registered User',
        self::GUEST_USER => 'Guest User',
    ];
}
