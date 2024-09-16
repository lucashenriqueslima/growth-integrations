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
use App\Services\Auvo\AuvoService;
use App\Traits\AuvoHttpClient;

class SendRequestToCreateAuvoTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected AuvoTaskDTO $auvoTaskDTO,
    ) {}

    public function handle(): void
    {

        // try {
        //     $auvoService = new AuvoService($this->auvoDepartment);

        //     $response = $auvoService->post('tasks/', $this->auvoTaskDTO);

        //     if (!in_array($response->status(), [200, 201])) {
        //         Log::error("Error creating task for customer {$this->auvoTaskDTO->externalId}: {$response->body()}");
        //     }
        // } catch (\Exception $e) {
        // }
    }
}
