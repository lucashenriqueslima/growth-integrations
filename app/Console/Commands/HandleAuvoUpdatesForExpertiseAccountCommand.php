<?php

namespace App\Console\Commands;

use App\Domains\AuvoAccountDataEnvironment;
use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoExpertiseCustomerGroup;
use App\Jobs\UpdateAuvoCustomerJob;
use App\Jobs\UpdateAuvoTaskJob;
use App\Services\Auvo\AuvoAuthService;
use Illuminate\Console\Command;
use App\Services\Auvo\AuvoService;
use Laravel\Octane\Facades\Octane;

class HandleAuvoUpdatesForExpertiseAccountCommand extends Command
{
    protected $signature = 'auvo:expertise-update';
    protected $description = 'Auvo customer update';

    protected AuvoAccountDataEnvironment $auvoAccountDataEnvironment;
    public function handle()
    {

        $this->auvoAccountDataEnvironment = new AuvoAccountDataEnvironment(
            apiKey: env('AUVO_API_KEY_EXPERTISE'),
            apiToken: env('AUVO_API_TOKEN_EXPERTISE'),
            manager: 'Victor Cândido',
            idUserFrom: 170642,
        );

        $accessTokenForAuvoAPI = (new AuvoAuthService(
            $this->auvoAccountDataEnvironment->apiKey,
            $this->auvoAccountDataEnvironment->apiToken,
        ))->getAccessToken();

        $auvoService = new AuvoService($accessTokenForAuvoAPI);
        [$solidyCustomers, $motoclubCustomers] = $auvoService->getIlevaDatabaseCustomersForExpertiseAuvoAccount();


        foreach ($solidyCustomers as $customer) {
            $auvoService->dispatchUpdateJobs(
                auvoCustomerDTO: new AuvoCustomerDTO(
                    externalId: $customer->external_id,
                    description: $customer->description,
                    name: $customer->name,
                    address: $customer->address,
                    manager: $this->auvoAccountDataEnvironment->manager,
                    note: $customer->description,
                    groupsId: [AuvoExpertiseCustomerGroup::getCustomerGroupByName($customer->customer_group)]
                ),
                auvoTaskDTO: new AuvoTaskDTO(
                    externalId: $customer->external_id,
                    idUserTo: $this->auvoAccountDataEnvironment->idUserFrom,
                    idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                    orientation: $customer->description,
                    address: $customer->address,
                    taskDate: $customer->task_date,
                    // teamId: 25744,
                    taskType: 161234,
                )

            );
        }

        foreach ($motoclubCustomers as $customer) {
            $auvoService->dispatchUpdateJobs(
                auvoCustomerDTO: new AuvoCustomerDTO(
                    externalId: $customer->external_id,
                    description: $customer->description,
                    name: $customer->name,
                    address: $customer->address,
                    manager: $this->auvoAccountDataEnvironment->manager,
                    note: $customer->note,
                ),
                auvoTaskDTO: new AuvoTaskDTO(
                    externalId: $customer->external_id,
                    idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                    idUserTo: $this->auvoAccountDataEnvironment->idUserFrom,
                    orientation: $customer->description,
                    address: $customer->address,
                )
            );
        }

        $this->info('Auvo customers updated successfully.');
        $this->logExecution();
    }

    protected function logExecution()
    {
        $timestamp = now()->toDateTimeString();
        $this->info("Command 'auvo-customer-update' was executed at {$timestamp}");
    }
}
