<?php

namespace App\Enums;

enum AuvoTrackingIdUserTo: int
{
    case Localizo = 170468;
    case Protec = 170480;

    #get team by name
    public static function getIdUserToByName(string $teamName): ?int
    {
        return match ($teamName) {
            'localizo' => self::Localizo->value,
            'protec' => self::Protec->value,
            default => null,
        };
    }
}
