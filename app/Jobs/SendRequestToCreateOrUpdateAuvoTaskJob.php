<?php

namespace App\Jobs;

use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Traits\AuvoHttpClient;

class SendRequestToCreateOrUpdateAuvoTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AuvoHttpClient;

    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
    ) {}

    public function handle(): void
    {

        try {
            Log::info("Creating task for customer " . json_encode($this->auvoTaskDTO->toArray()));
            $response = $this->put('tasks/', $this->auvoTaskDTO);

            Log::info("Creating task for customer {$this->auvoTaskDTO->taskDate}: {$response->body()}");

            // if (!in_array($response->status(), [200, 201])) {
            //     Log::error("Error creating task for customer {$this->auvoTaskDTO->externalId}: {$response->body()}");
            // }


        } catch (\Exception $e) {
            Log::error("Exception creating task for customer {$this->auvoTaskDTO->externalId}: " . $e->getMessage());
        }
    }
}
