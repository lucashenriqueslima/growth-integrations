<?php

namespace App\Jobs;

use App\DTO\AuvoCustomerDTO;
use App\Enums\AuvoDepartment;
use App\Models\AuvoCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrCreateAuvoCustomerJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoCustomerDTO $auvoCustomerDTO,
    ) {
        //
    }

    public function handle(): void
    {
        AuvoCustomer::updateOrCreate(
            [
                'auvo_department' => $this->auvoDepartment,
                'external_id' => $this->auvoCustomerDTO->externalId,
            ],
            $this->auvoCustomerDTO->toArrayDB()
        );
    }
}
