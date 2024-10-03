<?php

namespace App\Enums;

enum AuvoTrackingCustomerGroup: int
{
    case Localizo = 122513;
    case Protec = 122515;


    #get team by name
    public static function getCustomerGroupByName(string $teamName): ?int
    {
        return match ($teamName) {
            'localizo' => self::Localizo->value,
            'protec' => self::Protec->value,
            default => null,
        };
    }
}
