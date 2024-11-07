<?php

namespace App\Traits;

use App\Services\VeloTrack\VeloTrackClientService;
use GuzzleHttp\Client;

trait AuvoIntegration
{
    private Client $httpClient;
    public function handleHttpClient(): void
    {
        $this->httpClient = new (VeloTrackClientService(env('VELOTRACK_API_URL')))->getClient();
    }
}
