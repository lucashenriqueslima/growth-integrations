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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
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

        Cache::put("auvo_access_token_{$this->auvoDepartment->value}", $auvoAuthService->getAccessToken());
    }
    private function getConfiguredHttpClient(): PendingRequest
    {
        return Http::baseUrl(env('AUVO_API_URL'))
            ->withHeaders([
                'Authorization' => "Bearer " . Cache::get("auvo_access_token_{$this->auvoDepartment->value}"),
                'Content-Type' => 'application/json',
            ])
            ->timeout(40);
    }

    private function getClientConfig(): array
    {
        return [
            'Authorization' => "Bearer " . Cache::get("auvo_access_token_{$this->auvoDepartment->value}"),
            'Content-Type' => 'application/json',
        ];
    }

    private function getParamFilter(string $externalId): string
    {
        return "/?paramFilter=%7B'externalId':'$externalId','startDate':'2024-01-01T00:00:00','endDate':'2024-12-31T00:00:00'%7D&page=1&pageSize=1&order=asc";
    }

    public function sendRequestToCreateCustomer(): ?Response
    {
        try {

            $this->httpClient = $this->getConfiguredHttpClient();

            $data = $this->auvoCustomerDTO->toArray();

            $result = $this->httpClient->post('customers', $data);

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->put('customers', $data);
            }

            if (!in_array($result->status(), [200, 201])) {
                return $result;
            }

            return $result;
        } catch (ConnectionException $e) {
            $this->release(30);
        } catch (\Exception $e) {
            // dd($e);
            Log::error("Exception: {$e->getMessage()}");
            return null;
        }
    }

    public function sendRequestToCreateTask(): ?Response
    {
        try {
            $this->httpClient = $this->getConfiguredHttpClient();

            $data = $this->auvoTaskDTO->toArray();

            $result = $this->httpClient->post('tasks', $data);

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
        } catch (ConnectionException $e) {
            $this->release(30);
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
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
                return $result;
            }

            return $result;
        } catch (ConnectionException $e) {
            $this->release(30);
        } catch (\Exception $e) {
            // dd($e);
            Log::error("Exception: {$e->getMessage()}");
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
        } catch (ConnectionException $e) {
            $this->release(30);
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
    }

    public function getListCustomers(): ?Response
    {
        try {
            $this->httpClient = $this->getConfiguredHttpClient();

            $result = $this->httpClient->get("customers{$this->getParamFilter($this->auvoTaskDTO->externalId)}",);

            dd($result);

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->get("customers{$this->getParamFilter($this->auvoTaskDTO->externalId)}",);
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {$result->body()}");

                return $result;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
    }

    public function getListTasks(): ?Response
    {
        try {
            $this->httpClient = $this->getConfiguredHttpClient();

            $result = $this->httpClient->get('tasks', $this->getParamFilter($this->auvoTaskDTO->externalId));

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->get('tasks', $this->getParamFilter($this->auvoTaskDTO->externalId));
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {$result->body()}");

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
