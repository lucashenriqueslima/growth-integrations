<?php

namespace App\Console\Commands;

use App\Domains\AuvoAccountDataEnvironment;
use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Enums\AuvoTrackingCustomerGroup;
use App\Enums\AuvoExpertiseTeam;
use App\Enums\AuvoTrackingIdUserTo;
use App\Enums\AuvoTrackingOrientation;
use App\Enums\AuvoTrackingTaskType;
use App\Enums\AuvoTrackingTeam;
use App\Helpers\FormatHelper;
use App\Jobs\SendRequestToCreateOrUpdateAuvoCustomerJob;
use App\Services\Auvo\AuvoAuthService;
use Illuminate\Support\Facades\Bus;
use App\Services\Auvo\AuvoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HandleAuvoUpdatesForTrackingAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auvo:tracking-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected AuvoAccountDataEnvironment $auvoAccountDataEnvironment;


    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->auvoAccountDataEnvironment = new AuvoAccountDataEnvironment(
            apiKey: config('auvo_api.tracking.api_key'),
            apiToken: config('auvo_api.tracking.api_token'),
            manager: 'Rodrigo',
            idUserFrom: 170464,
        );

        $accessTokenForAuvoAPI = (
            new AuvoAuthService(
                $this->auvoAccountDataEnvironment->apiKey,
                $this->auvoAccountDataEnvironment->apiToken,
            )
        )->getAccessToken();



        $customers = AuvoService::getIlevaDatabaseCustomersForTrackingAuvoAccount();

        Cache::put('auvo_access_token', $accessTokenForAuvoAPI);

        $auvoService = new AuvoService(
            auvoDepartment: AuvoDepartment::Tracking,
            accessToken: $accessTokenForAuvoAPI,
        );

        // foreach ($customers as $customer) {
        //     Bus::chain([
        //         new SendRequestToCreateOrUpdateAuvoCustomerJob(
        //             auvoDepartment: AuvoDepartment::Tracking,
        //             auvoCustomerDTO: new AuvoCustomerDTO(
        //                 externalId: $customer->external_id,
        //                 description: $customer->description,
        //                 name: $customer->name,
        //                 address: $customer->address,
        //                 manager: $this->auvoAccountDataEnvironment->manager,
        //                 note: $customer->description,
        //                 groupsId: [AuvoTrackingCustomerGroup::getCustomerGroupByName($customer->team)],
        //                 phoneNumber: [FormatHelper::phone((string) $customer->phone_number)],
        //             ),
        //             auvoTaskDTO: new AuvoTaskDTO(
        //                 externalId: $customer->external_id,
        //                 idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
        //                 idUserTo: AuvoTrackingIdUserTo::getIdUserToByName($customer->team),
        //                 orientation: AuvoTrackingOrientation::getOrientationByName($customer->task_type),
        //                 address: $customer->address,
        //                 // teamId: AuvoTrackingTeam::getTeamByName($customer->team),
        //                 keyWords: [135995],
        //                 taskDate: AuvoTrackingTaskType::getTaskTypeByName($customer->task_type) == AuvoTrackingTaskType::Install ? $customer->task_date : null,
        //                 taskType: AuvoTrackingTaskType::getTaskTypeByName($customer->task_type)->value,
        //             ),
        //             isToCreateAuvoTask: true,
        //         )
        //     ])->dispatch();
        // }

        foreach ($customers as $customer) {
            $auvoService->dispatchUpdateJobs(
                auvoCustomerDTO: new AuvoCustomerDTO(
                    externalId: $customer->external_id,
                    description: $customer->description,
                    name: $customer->name,
                    address: $customer->address,
                    manager: $this->auvoAccountDataEnvironment->manager,
                    note: $customer->description,
                    groupsId: [AuvoTrackingCustomerGroup::getCustomerGroupByName($customer->team)],
                    phoneNumber: [FormatHelper::phone((string) $customer->phone_number)],
                    cpfCnpj: $customer->cpf,
                ),
                auvoTaskDTO: new AuvoTaskDTO(
                    externalId: $customer->external_id,
                    idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                    idUserTo: AuvoTrackingIdUserTo::getIdUserToByName($customer->team),
                    orientation: AuvoTrackingOrientation::getOrientationByName($customer->task_type),
                    address: $customer->address,
                    // teamId: AuvoTrackingTeam::getTeamByName($customer->team),
                    keyWords: [135995],
                    taskDate: AuvoTrackingTaskType::getTaskTypeByName($customer->task_type) == AuvoTrackingTaskType::Install ? $customer->task_date : null,
                    taskType: AuvoTrackingTaskType::getTaskTypeByName($customer->task_type)->value,
                ),
            );
        }
    }
}
