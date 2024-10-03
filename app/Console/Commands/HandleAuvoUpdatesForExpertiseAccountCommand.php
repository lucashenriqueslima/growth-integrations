<?php

namespace App\Console\Commands;

use App\Domains\AuvoAccountDataEnvironment;
use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Enums\AuvoExpertiseCustomerGroup;
use App\Jobs\Expertise\SendRequestToCreateAuvoExpertiseCustomerJob;
use App\Jobs\UpdateAuvoCustomerJob;
use App\Jobs\UpdateAuvoTaskJob;
use App\Services\Auvo\AuvoAuthService;
use Illuminate\Console\Command;
use App\Services\Auvo\AuvoService;
use Illuminate\Support\Facades\Cache;
use Laravel\Octane\Facades\Octane;

class HandleAuvoUpdatesForExpertiseAccountCommand extends Command
{
    protected $signature = 'auvo:expertise-update';
    protected $description = 'Auvo customer update';

    protected AuvoAccountDataEnvironment $auvoAccountDataEnvironment;
    public function handle()
    {

        $this->auvoAccountDataEnvironment = new AuvoAccountDataEnvironment(
            apiKey: config('auvo_api.expertise.api_key'),
            apiToken: config('auvo_api.expertise.api_token'),
            manager: 'Victor Cândido',
            idUserFrom: 170642,
        );

        $auvoAccessToken = (new AuvoAuthService(
            $this->auvoAccountDataEnvironment->apiKey,
            $this->auvoAccountDataEnvironment->apiToken,
        ))->getAccessToken();

        [$solidyCustomers, $motoclubCustomers] = AuvoService::getIlevaDatabaseCustomersForExpertiseAuvoAccount();

        $auvoDepartment = AuvoDepartment::Expertise;

        Cache::put("auvo_access_token_{$auvoDepartment->value}", $auvoAccessToken);

        foreach ($solidyCustomers as $customer) {

            dispatch(
                new SendRequestToCreateAuvoExpertiseCustomerJob(
                    auvoDepartment: $auvoDepartment,
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
                        // taskDate: $customer->task_date,
                        taskType: 161234,
                    ),
                )
            );
        }

        foreach ($motoclubCustomers as $customer) {
            new SendRequestToCreateAuvoExpertiseCustomerJob(
                auvoDepartment: $auvoDepartment,
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
                    taskType: 161234,
                )
            );
        }
    }
}
