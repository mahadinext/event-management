<?php
namespace App\Constants;

class EventConstants {
    // Event Status
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    
    // Registration Types
    const REGISTRATION_USER_ONLY = 1;
    const REGISTRATION_ALL_ALLOWED = 2;

    // Event Types
    const EVENT_TYPE_FREE = 1;
    const EVENT_TYPE_PAID = 2;
    
    // Status Labels
    public const STATUS_LABELS = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive'
    ];
    
    // Registration Type Labels
    public const REGISTRATION_TYPE_LABELS = [
        self::REGISTRATION_USER_ONLY => 'User Only',
        self::REGISTRATION_ALL_ALLOWED => 'All Allowed',
    ];

    // Event Type Labels
    public const EVENT_TYPE_LABELS = [
        self::EVENT_TYPE_FREE => 'Free',
        self::EVENT_TYPE_PAID => 'Paid'
    ];
}
