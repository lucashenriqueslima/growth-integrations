<?php

namespace App\Jobs\Tracking;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Models\AuvoCustomer;
use App\Traits\AuvoIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRequestToCreateAuvoTrackingCustomerJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels, AuvoIntegration;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected AuvoDepartment $auvoDepartment,
        protected AuvoCustomerDTO $auvoCustomerDTO,
        protected AuvoTaskDTO $auvoTaskDTO,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = $this->sendRequestToCreateOrUpdateCustomer();

            if (!in_array($response->status(), [200, 201])) {

                Log::error("Error updating customer " . json_encode($this->auvoCustomerDTO) . "; Message: {$response->body()}");

                if ($response->json()[0]["errorCode"] != 143) {
                    Log::error($response->json());
                    return;
                }
                // $customer = AuvoCustomer::where('external_id', 'like', "%{$this->auvoCustomerDTO->cpfCnpj}%")->first();

                // $this->auvoCustomerDTO->customerId = $customer->customer_id;
                // $this->auvoTaskDTO->customerId = $customer->customer_id;
                // $this->auvoTaskDTO->auvoCostumerId = $customer->id;

                $this->auvoCustomerDTO->cpfCnpj = null;

                $response = $this->sendRequestToCreateOrUpdateCustomer();

                $this->auvoCustomerDTO->customerId = $response->json()['result']['id'];
                $this->auvoTaskDTO->customerId = $response->json()['result']['id'];

                $customer = $this->updateOrCreateCustomer();

                $this->auvoTaskDTO->auvoCostumerId = $customer->id;

                dispatch(
                    new SendRequestToCreateAuvoTrackingTaskJob(
                        $this->auvoDepartment,
                        $this->auvoCustomerDTO,
                        $this->auvoTaskDTO,
                    )
                );


                dispatch(
                    new SendRequestToCreateAuvoTrackingTaskJob(
                        $this->auvoDepartment,
                        $this->auvoCustomerDTO,
                        $this->auvoTaskDTO,
                    )
                );

                return;
            }

            $this->auvoCustomerDTO->customerId = $response->json()['result']['id'];
            $this->auvoTaskDTO->customerId = $response->json()['result']['id'];

            $customer = $this->updateOrCreateCustomer();

            $this->auvoTaskDTO->auvoCostumerId = $customer->id;

            dispatch(
                new SendRequestToCreateAuvoTrackingTaskJob(
                    $this->auvoDepartment,
                    $this->auvoCustomerDTO,
                    $this->auvoTaskDTO,
                )
            );
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
        }
    }
}
