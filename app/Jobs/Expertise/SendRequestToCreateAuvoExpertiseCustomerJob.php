<?php

namespace App\Jobs\Expertise;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Models\AuvoTask;
use App\Traits\AuvoIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRequestToCreateAuvoExpertiseCustomerJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels, AuvoIntegration;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoCustomerDTO $auvoCustomerDTO,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = $this->sendRequestToCreateOrUpdateCustomer();

            $this->auvoCustomerDTO->customerId = $response->json()['result']['id'];
            $this->auvoTaskDTO->customerId = $response->json()['result']['id'];

            $customer = $this->updateOrCreateCustomer();

            $this->auvoTaskDTO->auvoCostumerId = $customer->id;

            if (
                AuvoTask::where('external_id', $this->auvoTaskDTO->externalId)
                ->where('auvo_department', $this->auvoDepartment->value)
                ->exists()
            ) {
                return;
            }


            dispatch(
                new SendRequestToCreateAuvoExpertiseTaskJob(
                    $this->auvoDepartment,
                    $this->auvoCustomerDTO,
                    $this->auvoTaskDTO,
                )
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
