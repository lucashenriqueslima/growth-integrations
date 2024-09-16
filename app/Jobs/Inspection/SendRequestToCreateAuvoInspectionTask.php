<?php

namespace App\Jobs\Inspection;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Traits\AuvoIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRequestToCreateAuvoInspectionTask implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels, AuvoIntegration;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoCustomerDTO $auvoCustomerDTO,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = $this->sendRequestToCreateOrUpdateTask();

            $this->auvoTaskDTO->taskId = $response->json()['result']['taskID'] ?? $response->json()['result'][0]['taskID'];

            $this->updateOrCreateTask();
        } catch (\Exception $e) {
            Log::error("Error creating task " . json_encode($this->auvoTaskDTO) . "; Message: {$e->getMessage()}");
        }
    }
}
