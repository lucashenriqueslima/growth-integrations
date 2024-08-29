<?php

namespace App\Console\Commands;

use App\DTO\AuvoCustomerDTO;
use App\Services\Auvo\AuvoAuthService;
use App\Services\Auvo\AuvoData;
use Illuminate\Console\Command;
use App\Services\Auvo\AuvoService;
use Laravel\Octane\Facades\Octane;

class HandleAuvoUpdatesForInspectionAccountCommand extends Command
{
    protected $signature = 'auvo-customer-update';
    protected $description = 'Auvo customer update';

    public function handle()
    {
        $accessTokenForAuvoAPI = (new AuvoAuthService(
            env('AUVO_API_KEY_INSPECTION'),
            env('AUVO_API_TOKEN_INSPECTION')
        ))->getAccessToken();

        $auvoService = new AuvoService($accessTokenForAuvoAPI);
        [$solidyCustomers, $motoclubCustomers, $novaCustomers] = $auvoService->getIlevaDatabaseCustomersForInspectionAuvoAccount();
        // $auvoService->updateCustomers($solidyCustomers);
        // $auvoService->updateCustomers($motoclubCustomers, 'mc');

        $tasksData = (new AuvoData())->getAuvoData();

        foreach ($solidyCustomers as $customer) {
            $auvoService->dispatchUpdateJobs(
                new AuvoCustomerDTO(
                    externalId: $customer->id,
                    description: $customer->name,
                    name: "{$customer->id}{$customer->name}",
                    address: $customer->address,
                    manager: 'thais santos',
                    note: $customer->note,
                    workshopId: $customer->id_oficina,
                ),
            );
        }

        foreach ($motoclubCustomers as $customer) {
            $auvoService->dispatchUpdateJobs(
                new AuvoCustomerDTO(
                    externalId: "mc{$customer->id}",
                    description: $customer->name,
                    name: "{$customer->id}{$customer->name}",
                    address: $customer->address,
                    manager: 'thais santos',
                    note: $customer->note,
                    workshopId: $customer->id_oficina,
                ),
            );
        }

        foreach ($novaCustomers as $customer) {
            $auvoService->dispatchUpdateJobs(
                new AuvoCustomerDTO(
                    externalId: "nv{$customer->id}",
                    description: $customer->name,
                    name: "{$customer->id}{$customer->name}",
                    address: $customer->address,
                    manager: 'thais santos',
                    note: $customer->note,
                    workshopId: $customer->id_oficina,
                ),
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
