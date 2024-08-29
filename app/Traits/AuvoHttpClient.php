<?php

namespace App\Traits;

use App\Services\Auvo\AuvoAuthService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

trait AuvoHttpClient
{

    private PendingRequest $httpClient;
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->httpClient = $this->getConfiguredHttpClient();
    }

    public function put(string $url, array $data): ?Response
    {
        try {
            $result = $this->httpClient->put($url, $data);

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->put($url, $data);
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {${json_encode($data)}}: {$result->body()}");

                return null;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
    }

    private function renewAccessToken(): void
    {
        $auvoAuthService = new AuvoAuthService(
            $this->apiKey,
            $this->apiToken,
        );

        Cache::put('auvo_access_token', $auvoAuthService->getAccessToken(), 60);
    }
    private function getConfiguredHttpClient(): PendingRequest
    {
        return Http::baseUrl(env('AUVO_API_URL'))
            ->withHeaders([
                'Authorization' => "Bearer {${Cache::get('auvo_access_token')}}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(40);
    }
}
