<?php

namespace App\Enums;

enum AuvoTrackingTaskType: int
{
    case Install = 161122;
    case Remove = 161123;
    case ChangeOwnership = 161112;

    #get team by name
    public static function getTaskTypeByName(string $teamName): ?self
    {
        return match ($teamName) {
            'instalação' => self::Install,
            'remoção' => self::Remove,
            'troca_titularidade' => self::ChangeOwnership,
            default => null,
        };
    }
}
