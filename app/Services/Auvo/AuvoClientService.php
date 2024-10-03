<?php

namespace App\Services\Auvo;

use GuzzleHttp\Client;

class AuvoClientService
{
    private static Client $client;

    public function __construct(): Client
    {
        return static::$client ??= new Client(
            [
                'base_uri' => env('AUVO_API_URL'),
            ]
        );
    }
}
