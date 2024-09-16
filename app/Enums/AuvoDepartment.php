<?php

namespace App\Enums;

enum AuvoDepartment: string
{
    case Expertise = 'expertise';
    case Inspection = 'inspection';
    case Tracking = 'tracking';

    public function getApiKey(): string
    {
        return match ($this) {
            self::Expertise => env('AUVO_API_KEY_EXPERTISE'),
            self::Inspection => env('AUVO_API_KEY_INSPECTION'),
            self::Tracking => env('AUVO_API_KEY_TRACKING'),
            default => 'Unknown',
        };
    }

    public function getApiToken(): string
    {
        return match ($this) {
            self::Expertise => env('AUVO_API_TOKEN_EXPERT'),
            self::Inspection => env('AUVO_API_TOKEN_INSPECTION'),
            self::Tracking => env('AUVO_API_TOKEN_TRACKING'),
            default => 'Unknown',
        };
    }
}
