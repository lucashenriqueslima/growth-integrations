<?php

namespace App\Jobs;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Helpers\ValidationHelper;
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
        protected readonly ?AuvoTaskDTO $auvoTaskDTO = null,
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

            if (!$this->auvoTaskDTO) {
                return;
            }

            $this->auvoTaskDTO->customerId = $response->json()['result']['id'];

            dispatch(new UpdateAuvoTaskJob(
                $this->accessToken,
                $this->auvoTaskDTO,
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
