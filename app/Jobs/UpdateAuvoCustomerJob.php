<?php

namespace App\Jobs;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Helpers\ValidationHelper;
use App\Models\AuvoCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UpdateAuvoCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly string $accessToken,
        protected readonly AuvoCustomerDTO $auvoCustomerDTO,
        protected AuvoTaskDTO $auvoTaskDTO,
        protected readonly AuvoDepartment $auvoDepartment,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PendingRequest $rawClient): void
    {
        $client = $rawClient->baseUrl(env('AUVO_API_URL'))
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ]);

        try {
            $response = $client->put(
                'customers',
                $this->auvoCustomerDTO->toArray(),
            );

            if (!in_array($response->status(), [200, 201])) {
                Log::error("Error updating customer " . json_encode($this->auvoCustomerDTO) . "; Message: {$response->body()}");
                return;
            }

            $this->auvoCustomerDTO->customerId = $response->json()['result']['id'];
            $this->auvoTaskDTO->customerId = $response->json()['result']['id'];

            $customer = AuvoCustomer::updateOrCreate(
                [
                    'auvo_department' => $this->auvoDepartment,
                    'customer_id' => $this->auvoCustomerDTO->customerId,
                ],
                [
                    'external_id' => $this->auvoCustomerDTO->externalId,
                    'name' => $this->auvoCustomerDTO->name,
                ]
            );

            $this->auvoTaskDTO->auvoCostumerId = $customer->id;

            dispatch(new UpdateAuvoTaskJob(
                $this->accessToken,
                $this->auvoTaskDTO,
                $this->auvoDepartment,
            ));
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
