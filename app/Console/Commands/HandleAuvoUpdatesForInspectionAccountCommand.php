<?php

namespace App\Console\Commands;

use App\Domains\AuvoAccountDataEnvironment;
use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Jobs\Inspection\SendRequestToCreateAuvoInspectionCustomer;
use App\Models\Ileva\IlevaAccidentInvolved;
use App\Services\Auvo\AuvoAuthService;
use App\Services\Auvo\AuvoData;
use Illuminate\Console\Command;
use App\Services\Auvo\AuvoService;
use App\Services\GrowthApi\GrowthApiService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Laravel\Octane\Facades\Octane;

class HandleAuvoUpdatesForInspectionAccountCommand extends Command
{
    protected $signature = 'auvo:inspection-update';
    protected $description = 'Auvo customer update';
    protected AuvoAccountDataEnvironment $auvoAccountDataEnvironment;

    public function handle()
    {
        try {
            $this->auvoAccountDataEnvironment = new AuvoAccountDataEnvironment(
                apiKey: config('auvo_api.inspection.api_key'),
                apiToken: config('auvo_api.inspection.api_token'),
                manager: 'thais santos',
                idUserFrom: 163489,
            );



            $workshops = (new GrowthApiService())->getWorkshops();

            [$solidyCustomers, $motoclubCustomers, $novaCustomers] = Octane::concurrently([
                fn() => IlevaAccidentInvolved::getAccidentInvolvedForAuvoToSolidy(),
                fn() => IlevaAccidentInvolved::getAccidentInvolvedForAuvoToMotoclub(),
                fn() => IlevaAccidentInvolved::getAccidentInvolvedForAuvoToNova(),
            ], 50000);

            $auvoAccessToken = (new AuvoAuthService(
                $this->auvoAccountDataEnvironment->apiKey,
                $this->auvoAccountDataEnvironment->apiToken,
            ))->getAccessToken();

            $auvoDepartment = AuvoDepartment::Inspection;

            Cache::put("auvo_access_token_{$auvoDepartment->value}", $auvoAccessToken);

            foreach ($solidyCustomers as $solidyCustomer) {


                $workshop = Arr::first($workshops, fn($workshop) => $workshop['ileva_id'] === $solidyCustomer->workshop_id);

                dispatch(
                    new SendRequestToCreateAuvoInspectionCustomer(
                        AuvoDepartment::Inspection,
                        new AuvoCustomerDTO(
                            externalId: $solidyCustomer->external_id,
                            description: $solidyCustomer->name,
                            name: $solidyCustomer->name,
                            address: $solidyCustomer->address,
                            note: $solidyCustomer->note,
                            manager: $this->auvoAccountDataEnvironment->manager,
                        ),
                        new AuvoTaskDTO(
                            externalId: $solidyCustomer->external_id,
                            address: $solidyCustomer->address,
                            idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                            idUserTo: $workshop['collaborator']['auvo_id'] ?? null,
                            orientation: $solidyCustomer->orientation,
                            questionnaireId: 173499,
                        ),
                        $solidyCustomer->start_date,
                        $workshop,
                    )
                );
            }

            foreach ($motoclubCustomers as $motoclubCustomer) {


                $workshop = Arr::first($workshops, fn($workshop) => $workshop['ileva_id'] === $motoclubCustomer->workshop_id);

                dispatch(
                    new SendRequestToCreateAuvoInspectionCustomer(
                        AuvoDepartment::Inspection,
                        new AuvoCustomerDTO(
                            externalId: $motoclubCustomer->external_id,
                            description: $motoclubCustomer->name,
                            name: $motoclubCustomer->name,
                            address: $motoclubCustomer->address,
                            note: $motoclubCustomer->note,
                            manager: $this->auvoAccountDataEnvironment->manager,
                        ),
                        new AuvoTaskDTO(
                            externalId: $motoclubCustomer->external_id,
                            address: $motoclubCustomer->address,
                            idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                            idUserTo: $workshop['collaborator']['auvo_id'] ?? null,
                            orientation: $motoclubCustomer->orientation,
                            questionnaireId: 173499
                        ),
                        $solidyCustomer->start_date,
                        $workshop,

                    )
                );
            }

            foreach ($novaCustomers as $novaCustomer) {


                $workshop = Arr::first($workshops, fn($workshop) => $workshop['ileva_id'] === $novaCustomer->workshop_id);

                dispatch(
                    new SendRequestToCreateAuvoInspectionCustomer(
                        AuvoDepartment::Inspection,
                        new AuvoCustomerDTO(
                            externalId: $novaCustomer->external_id,
                            description: $novaCustomer->name,
                            name: $novaCustomer->name,
                            address: $novaCustomer->address,
                            note: $novaCustomer->note,
                            manager: $this->auvoAccountDataEnvironment->manager,
                        ),
                        new AuvoTaskDTO(
                            externalId: $novaCustomer->external_id,
                            address: $novaCustomer->address,
                            idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                            idUserTo: $workshop['collaborator']['auvo_id'] ?? null,
                            orientation: $novaCustomer->orientation,
                            questionnaireId: 173499
                        ),
                        $solidyCustomer->start_date,
                        $workshop,

                    )
                );
            }
        } catch (\Exception $e) {
            dd($e);
        }


        // foreach ($solidyCustomers as $solidyCustomer) {


        //     dd($workshop);

        //     dispatch(new SendRequestToCreateAuvoInspectionCustomer(
        //         new AuvoCustomerDTO(
        //             externalId: $solidyCustomer->external_id,
        //             description: $solidyCustomer->name,
        //             name: $solidyCustomer->name,
        //             address: $solidyCustomer->address,
        //             note: $solidyCustomer->note,
        //             workshopId: $workshops->firstWhere('name', 'Solidy')->id,
        //         ),
        //         new AuvoTaskDTO(
        //             externalId: $solidyCustomer->external_id,
        //         ),
        //         $solidyCustomer->workshop_id,
        //     ),);
        // }
    }


    // protected function getCollaboratorByWorkshopId(int $workshopId): string
    // {
    //     $workshop = $this->auvoAccountDataEnvironment->workshops->firstWhere('id', $workshopId);
    //     return $workshop->collaborator;
    // }
}
