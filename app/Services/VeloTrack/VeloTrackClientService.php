<?php

namespace App\Services\VeloTrack;

use App\Contracts\ClientServiceContract;
use GuzzleHttp\Client;

class VeloTrackClientService implements ClientServiceContract
{
    private static Client $client;

    public function __construct(string $baseUrl)
    {
        static::$client ??= new Client(
            [
                'base_uri' => $baseUrl,
            ]
        );
    }

    public function getClient(): Client
    {
        return static::$client;
    }
}
