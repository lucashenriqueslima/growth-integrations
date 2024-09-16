<?php

namespace App\Jobs;

use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Models\AuvoCustomer;
use App\Models\AuvoTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrCreateAuvoTaskJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        AuvoTask::updateOrCreate(
            [
                'auvo_dapartment' => $this->auvoDepartment,
                'external_id' => $this->auvoTaskDTO->externalId,
            ],
            $this->auvoTaskDTO->toArrayDB()
        );
    }
}
