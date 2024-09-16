<?php

namespace App\Services\Auvo;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Helpers\FormatHelper;
use App\Jobs\UpdateAuvoCustomerJob;
use App\Jobs\UpdateAuvoTaskJob;
use App\Models\AuvoCustomer;
use App\Models\AuvoTask;
use App\Models\Ileva\IlevaAccidentInvolved;
use App\Models\Ileva\IlevaAssociateVehicle;
use Laravel\Octane\Facades\Octane;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\UpdateOrCreateAuvoCustomerJob;
use App\Jobs\UdpateOrCreateAuvoTaskJob;
use App\Jobs\UpdateOrCreateAuvoTaskJob;
use Illuminate\Http\Client\Response;

class AuvoService
{
    private PendingRequest $httpClient;

    public function __construct(
        private AuvoDepartment $auvoDepartment,
        private string $accessToken,
    ) {
        $this->httpClient = $this->getConfiguredHttpClient();
    }
    public function getIlevaDatabaseCustomersForInspectionAuvoAccount(): array
    {

        return [
            IlevaAccidentInvolved::getAccidentInvolvedForAuvoToMotoclub('ileva'),
            IlevaAccidentInvolved::getAccidentInvolvedForAuvoToSolidy('ileva_motoclub'),
            IlevaAccidentInvolved::getAccidentInvolvedForAuvoToNova('ileva_nova'),
        ];
    }

    public static function getIlevaDatabaseCustomersForExpertiseAuvoAccount(): array
    {
        return Octane::concurrently([
            fn() => IlevaAccidentInvolved::getAccidentInvolvedForAuvoExpertiseInSolidy(),
            fn() => IlevaAccidentInvolved::getAccidentInvolvedForAuvoExpertiseInMotoclub(),
        ], 50000);
    }

    public static function getIlevaDatabaseCustomersForTrackingAuvoAccount(): array
    {
        return IlevaAssociateVehicle::getVehiclesForAuvoTrackingInSolidy();
    }

    public function dispatchUpdateJobs(
        AuvoCustomerDTO $auvoCustomerDTO,
        ?AuvoTaskDTO $auvoTaskDTO = null,
    ): void {
        dispatch(new UpdateAuvoCustomerJob(
            $this->accessToken,
            $auvoCustomerDTO,
            $auvoTaskDTO,
            $this->auvoDepartment,
        ));
    }

    public function post(string $url, object $objectDTO): ?Response
    {
        try {

            $data = $objectDTO->toArray();

            $result = $this->httpClient->post($url, $objectDTO->toArray());

            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->httpClient->post($url, $objectDTO->toArray());
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {${json_encode($data)}}: {$result->body()}");

                return $result;
            }

            $this->handleUpdateOrCreateObjectDTO($result, $objectDTO);
            return $result;
        } catch (\Exception $e) {
            Log::error("Exception: {${json_encode($data)}}: {$e->getMessage()}");
            return null;
        }
    }

    public function put(string $url, object $objectDTO): ?Response
    {
        try {
            $this->httpClient = $this->getConfiguredHttpClient();

            $data = $objectDTO->toArray();

            $result = $this->httpClient->put($url, $data);


            if ($result->status() === 401) {
                $this->renewAccessToken();
                $this->httpClient = $this->getConfiguredHttpClient();
                return $this->put($url, $objectDTO);
            }

            if (!in_array($result->status(), [200, 201])) {
                Log::error("Error: {${json_encode($data)}}: {$result->body()}");

                return $result;
            }

            // $this->handleUpdateOrCreateObjectDTO($result, $objectDTO);

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function handleUpdateOrCreateObjectDTO(Response $result, object $objectDTO): void
    {

        match (true) {
            $objectDTO instanceof AuvoCustomerDTO => (function () use ($objectDTO, $result) {

                $objectDTO->customerId = $result->json()['result']['id'];

                AuvoCustomer::updateOrCreate(
                    [
                        'auvo_department' => $this->auvoDepartment,
                        'external_id' => $objectDTO->externalId,
                    ],
                    $objectDTO->toArrayDB($this->auvoDepartment)
                );
            })(),
            $objectDTO instanceof AuvoTaskDTO => (function () use ($objectDTO, $result) {

                $objectDTO->taskId = (string) $result->json()['result'][0]['taskID'];

                // AuvoTask::updateOrCreate(
                //     [
                //         'auvo_department' => $this->auvoDepartment,
                //         'external_id' => $objectDTO->externalId,
                //     ],
                //     dd($objectDTO->toArrayDB())
                // );
            })(),
            default => Log::error('Unknown DTO class'),
        };
    }

    private function getConfiguredHttpClient(): PendingRequest
    {
        return Http::baseUrl(env('AUVO_API_URL'))
            ->withHeaders([
                'Authorization' => "Bearer " . (string) Cache::get('auvo_access_token'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(40);
    }

    private function renewAccessToken(): void
    {

        $auvoAuthService = new AuvoAuthService(
            $this->auvoDepartment->getApiKey(),
            $this->auvoDepartment->getApiToken(),
        );

        Cache::put('auvo_access_token', $auvoAuthService->getAccessToken());
    }
}
