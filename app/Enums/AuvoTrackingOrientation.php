<?php

namespace App\Enums;

enum AuvoTrackingOrientation: string
{
    case ToInstall = 'Para instalação';
    case ToRemove = 'Para remoção';
    case ToChangeOwnership = 'Para troca de titularidade';

    #get team by name
    public static function getOrientationByName(string $teamName): ?string
    {
        return match ($teamName) {
            'instalação' => self::ToInstall->value,
            'remoção' => self::ToRemove->value,
            'troca_titularidade' => self::ToChangeOwnership->value,
            default => null,
        };
    }
}
