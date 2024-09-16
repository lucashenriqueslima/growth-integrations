<?php

namespace App\Services\GrowthApi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GrowthApiService
{
    private PendingRequest $httpClient;
    public function __construct()
    {
        $this->httpClient = $this->getConfiguredHttpClient();
    }

    public function getWorkshops(): array
    {
        $response = $this->httpClient->get('workshops');

        return $response->json();
    }

    private function getConfiguredHttpClient(): PendingRequest
    {
        return Http::baseUrl('https://growthsolutions.com.br/api/auvo')
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->timeout(40);
    }
}
