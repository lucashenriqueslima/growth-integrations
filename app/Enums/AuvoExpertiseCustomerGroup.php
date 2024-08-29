<?php

namespace App\Enums;

enum AuvoExpertiseCustomerGroup: int
{
    case Solidy = 122524;
    case Motoclub = 122525;

    public static function getCustomerGroupByName(string $teamName): ?int
    {
        return match ($teamName) {
            'solidy' => self::Solidy->value,
            'motoclub' => self::Motoclub->value,
            default => null,
        };
    }
}
