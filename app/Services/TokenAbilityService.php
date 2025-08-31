<?php

namespace App\Services;

use Carbon\Carbon;

class TokenAbilityService
{
    public const ABILITIES = [
        'customer' => [
            'customer:profile',
            'customer:orders', 
            'customer:tickets',
            'customer:wishlist',
        ],
        'admin' => [
            'admin:profile',
            'admin:events',
            'admin:organizers',
            'admin:reports',
        ],
        'organizer' => [
            'organizer:profile',
            'organizer:events',
            'organizer:brands',
        ],
        'scan_point' => [
            'scan_point:profile',
            'scan_point:scan',
        ]
    ];

    public static function getAbilitiesFor(string $userType): array
    {
        return self::ABILITIES[$userType] ?? [];
    }
}