<?php

namespace App\Services\Auvo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AuvoAuthService
{
    private PendingRequest $client;
    private array $authencationData;
    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiToken,
    ) {
        $baseUrl = env('AUVO_API_URL', 'https://api.auvo.com.br/v2');

        $this->client = Http::baseUrl($baseUrl)
            ->withHeaders([
                "Content-Type" => "application/json",
            ]);

        $this->authencationData = $this->authenticate();
    }


    public function getAccessToken(): string
    {
        return $this->authencationData['accessToken'];
    }

    private function authenticate(): array
    {
        try {
            $response = $this->client->get('login', [
                'apiKey' => $this->apiKey,
                'apiToken' => $this->apiToken,
            ]);

            return $response->json()['result'];
        } catch (\Exception $e) {
            dd($e);
            throw $e;
        }
    }
}
