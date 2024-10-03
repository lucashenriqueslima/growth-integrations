<?php

namespace App\Jobs;

use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Models\AuvoTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use Illuminate\Http\Client\RequestException;

class UpdateAuvoTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected readonly string $accessToken,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
        protected readonly AuvoDepartment $auvoDepartment,
    ) {}

    public function handle(): void
    {
        $client = $this->configureHttpClient();

        try {

            $externalId = $this->auvoTaskDTO->externalId;

            $response = $client->get("tasks/?paramFilter=%7B'externalId':'$externalId','startDate':'2024-01-01T00:00:00','endDate':'2024-12-31T00:00:00'%7D&page=1&pageSize=1&order=asc");
        } catch (RequestException $e) {
            Log::error("RequestException: {$e->getMessage()}");
            try {
                if ($e->getCode() != 404) {
                    return;
                }

                $response = $client->put('tasks/', $this->auvoTaskDTO->toArray());

                if (!in_array($response->status(), [200, 201])) {
                    Log::error("Error updating task " . json_encode($this->auvoTaskDTO) . "; Message: {$response->body()}");
                }

                $this->auvoTaskDTO->taskId = $response->json()['result']['taskID'] ?? $response->json()['result'][0]['taskID'];

                AuvoTask::updateOrCreate(
                    [
                        'task_id' => $this->auvoTaskDTO->taskId,
                        'auvo_department' => $this->auvoDepartment,
                    ],
                    [
                        'task_id' => $this->auvoTaskDTO->taskId,
                        'external_id' => $this->auvoTaskDTO->externalId,
                        'auvo_customer_id' => $this->auvoTaskDTO->auvoCostumerId,
                    ]
                );
            } catch (\Exception $e) {
                dd($e);
                Log::error("Exception: {$e->getMessage()}");
            }
        } catch (\Exception $e) {
            Log::error("Exception: {$e->getMessage()}");
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
