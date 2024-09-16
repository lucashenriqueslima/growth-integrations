<?php

namespace App\Traits;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Jobs\UpdateOrCreateAuvoCustomerJob;
use App\Jobs\UdpateOrCreateAuvoTaskJob;
use App\Jobs\UpdateOrCreateAuvoTaskJob;
use App\Models\AuvoCustomer;
use App\Models\AuvoTask;
use App\Services\Auvo\AuvoAuthService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use stdClass;

trait AuvoIntegration
{

    private PendingRequest $httpClient;

    private function renewAccessToken(): void
    {

        $auvoAuthService = new AuvoAuthService(
            $this->auvoDepartment->getApiKey(),
            $this->auvoDepartment->getApiToken(),
        );

        Cache::put('auvo_access_token', $auvoAuthService->getAccessToken());
    }
    private function getConfiguredHttpClient(): PendingRequest
    {
        return Http::baseUrl(env('AUVO_API_URL'))
            ->withHeaders([
                'Authorization' => "Bearer " . Cache::get('auvo_access_token'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(40);
    }

    public function sendRequestToCreateOrUpdateCustomer(): ?Response
    {
        try {

            $this->httpClient = $this->getConfiguredHttpClient();

            $data = $this->auvoCustomerDTO->toArray();

            $result = $this->httpClient->put('customers', $data);

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->put('customers', $data);
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {${json_encode($data)}}: {$result->body()}");

                return $result;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
    }

    public function sendRequestToCreateOrUpdateTask(): ?Response
    {
        try {
            $this->httpClient = $this->getConfiguredHttpClient();

            $data = $this->auvoTaskDTO->toArray();

            $result = $this->httpClient->put('tasks', $data);

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->put('tasks', $data);
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {${json_encode($data)}}: {$result->body()}");

                return $result;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
    }

    public function updateOrCreateCustomer(): AuvoCustomer
    {
        return AuvoCustomer::updateOrCreate(
            [
                'auvo_department' => $this->auvoDepartment,
                'customer_id' => $this->auvoCustomerDTO->customerId,
            ],
            [
                'external_id' => $this->auvoCustomerDTO->externalId,
                'name' => $this->auvoCustomerDTO->name,
            ]
        );
    }

    public function updateOrCreateTask(): AuvoTask
    {
        return AuvoTask::updateOrCreate(
            [
                'auvo_department' => $this->auvoDepartment,
                'task_id' => $this->auvoTaskDTO->taskId,
            ],
            [
                'external_id' => $this->auvoTaskDTO->externalId,
                'auvo_customer_id' => $this->auvoTaskDTO->auvoCostumerId,
            ]
        );
    }
}
