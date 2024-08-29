<?php

namespace App\Enums;

enum AuvoTrackingTeam: int
{
    case Localizo = 25195;
    case Protec = 25196;

    #get team by name
    public static function getTeamByName(string $teamName): ?int
    {
        return match ($teamName) {
            'localizo' => self::Localizo->value,
            'protec' => self::Protec->value,
            default => null,
        };
    }
}
