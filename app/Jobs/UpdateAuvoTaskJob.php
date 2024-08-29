<?php

namespace App\Jobs;

use App\DTO\AuvoTaskDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Task;

class UpdateAuvoTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected readonly string $accessToken,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
    ) {}

    public function handle(): void
    {
        $client = $this->configureHttpClient();

        try {
            Log::info("Creating task for customer " . json_encode($this->auvoTaskDTO->toArray()));
            $response = $client->put('tasks/', $this->auvoTaskDTO->toArray());

            Log::info("Creating task for customer {$this->auvoTaskDTO->taskDate}: {$response->body()}");

            if (!in_array($response->status(), [200, 201])) {
                Log::error("Error creating task for customer {$this->auvoTaskDTO->externalId}: {$response->body()}");
            }
        } catch (\Exception $e) {
            Log::error("Exception creating task for customer {$this->auvoTaskDTO->externalId}: " . $e->getMessage());
        }
    }

    private function configureHttpClient()
    {
        return Http::baseUrl(env('AUVO_API_URL'))
            ->withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->retry(3, 100);
    }

    private function processSuccessfulResponse($response)
    {
        $responseData = $response->json();
        if (isset($responseData['result']['taskID'])) {
            Task::create([
                'auvo_id_task' => $responseData['result']['taskID'],
            ]);
        } else {
            Log::error("Task ID not found in response: " . json_encode($responseData));
        }
    }
}
