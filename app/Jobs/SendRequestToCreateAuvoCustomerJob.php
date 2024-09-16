<?php

namespace App\Jobs;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Helpers\ValidationHelper;
use App\Services\Auvo\AuvoService;
use App\Traits\AuvoHttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendRequestToCreateAuvoCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AuvoHttpClient;

    const AUVO_API_URL = 'customers';
    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoCustomerDTO $auvoCustomerDTO,
        protected readonly ?AuvoTaskDTO $auvoTaskDTO = null,
        protected readonly ?bool $isToCreateAuvoTask = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // $auvoService = new AuvoService($this->auvoDepartment);

        try {
            // $response = $auvoService->post(
            //     self::AUVO_API_URL,
            //     $this->auvoCustomerDTO,
            // );

            // if ($response->status() !== 200) {
            //     Log::error("{${json_encode($this->auvoCustomerDTO->toArray())}}: {$response->body()}");
            //     return;
            // }

            // if (!$this->auvoTaskDTO) {
            //     return;
            // }


            // $this->auvoTaskDTO->customerId = $response->json()['result']['id'];
            // if ($this->isToCreateAuvoTask) {

            //     $this->prependToChain(
            //         new SendRequestToCreateAuvoTaskJob(
            //             auvoDepartment: $this->auvoDepartment,
            //             auvoTaskDTO: $this->auvoTaskDTO,
            //         )
            //     );
            // } else {


            //     $this->prependToChain(
            //         new SendRequestToCreateOrUpdateAuvoTaskJob(
            //             auvoDepartment: $this->auvoDepartment,
            //             auvoTaskDTO: $this->auvoTaskDTO,
            //         )
            //     );
            // }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }


    private function getCustomerCpfCnpj(): ?string
    {
        return ValidationHelper::cpfCnpj($this->auvoCustomerDTO->cpfCnpj) ? $this->auvoCustomerDTO->cpfCnpj : null;
    }

    private function getCustomerEmail(): ?array
    {
        return $this->auvoCustomerDTO->email ? ['email' => $this->auvoCustomerDTO->email] : null;
    }
}
