<?php

namespace App\Console\Commands;

use App\Domains\AuvoAccountDataEnvironment;
use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Jobs\SuccessAssociate\SendRequestToCreateAuvoSuccessAssociateCustomerJob;
use App\Services\Auvo\AuvoAuthService;
use App\Services\Auvo\AuvoService;
use App\Traits\AuvoIntegration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Type\Integer;

class HandleAuvoUpdatesForAssociateSuccessAccountCommand extends Command
{
    use AuvoIntegration;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auvo:associate-success-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    protected AuvoAccountDataEnvironment $auvoAccountDataEnvironment;
    protected AuvoDepartment $auvoDepartment;

    public function handle()
    {
        $this->auvoAccountDataEnvironment = new AuvoAccountDataEnvironment(
            apiKey: config('auvo_api.associate_success.api_key'),
            apiToken: config('auvo_api.associate_success.api_token'),
            manager: 'taiara santos',
            idUserFrom: 184410,
        );


        $auvoAccessToken = (new AuvoAuthService(
            $this->auvoAccountDataEnvironment->apiKey,
            $this->auvoAccountDataEnvironment->apiToken,
        ))->getAccessToken();

        $this->auvoDepartment = AuvoDepartment::AssociateSuccess;

        Cache::put("auvo_access_token_{$this->auvoDepartment->value}", $auvoAccessToken);

        $colaborators = $this->getListUsers()->json()['result']['entityList'];
        //Log::info($colaborators);
        $colaborators = $this->generateCollection($colaborators);
        
        [
            $solidyCustomers,
            //$solidyWorkshopOutCustomers,
            //$solidyWorkshopOutLateCustomers,
            $motoclubCustomers,
            //$motoclubWorkshopOutCustomers,
            //$motoclubWorkshopOutLateCustomers
        ] = AuvoService::getIlevaDatabaseCustomersForSuccessAssociateAuvoAccount();

        $remainingWorkdays = $this->getRemainingWorkdays();
        $sevenDays = $this->getNextSevenDays();

        $this->handleDistribution(
            $colaborators,
            $solidyCustomers,
            $remainingWorkdays,
            false
        );

        // $this->handleDistribution(
        //     $colaborators,
        //     $solidyWorkshopOutCustomers,
        //     $sevenDays,
        //     true
        // );

        // $this->handleDistribution(
        //     $colaborators,
        //     $solidyWorkshopOutLateCustomers,
        //     $sevenDays,
        //     true
        // );

        $this->handleDistribution(
            $colaborators,
            $motoclubCustomers,
            $remainingWorkdays,
            false
        );

        // $this->handleDistribution(
        //     $colaborators,
        //     $motoclubWorkshopOutCustomers,
        //     $sevenDays,
        //     true
        // );

        // $this->handleDistribution(
        //     $colaborators,
        //     $motoclubWorkshopOutLateCustomers,
        //     $sevenDays,
        //     true
        // );
    }

    private function handleDistribution(
        Collection $colaborators,
        Collection $customers,
        array $Workdays,
        bool $afeterWorkshop
    ) {
        
        $totalCollaborators = $colaborators->count();
        
        $customerChunks = $customers->chunk(ceil($customers->count() / $totalCollaborators));
        $customerChunks->each(function ($chunk, $index) use ($colaborators, $Workdays, $afeterWorkshop) {
            $colaborator = $colaborators->get($index);
        
            
            if (!$colaborator) {
                return;
            }
            
            // Distribui os dias para os clientes desse chunk
            $distributed = $this->distributeDates($chunk, $Workdays);
         
            // Envia os jobs
            // if($afeterWorkshop) {
            //     $this->handleDispatchJobsAfterWorkshop($distributed, $colaborator->userID);
            // } else {
            //     $this->handleDispatchJobs($distributed, $colaborator->userID);
            // }

            $this->handleDispatchJobs($distributed, $colaborator->userID);
            
        });
    }

    private function handleDispatchJobs(Collection $customers, ?int $idUserTo = null)
    {
        foreach ($customers as $customer) {
            dispatch(
                new SendRequestToCreateAuvoSuccessAssociateCustomerJob(
                    auvoDepartment: $this->auvoDepartment,
                    auvoCustomerDTO: new AuvoCustomerDTO(
                        externalId: $customer->external_id,
                        description: $customer->orientation,
                        name: $customer->name,
                        address: $customer->address,
                        manager: $this->auvoAccountDataEnvironment->manager,
                        note: $customer->orientation,

                    ),
                    auvoTaskDTO: new AuvoTaskDTO(
                        externalId: $customer->task_external_id,
                        idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                        idUserTo: $idUserTo,
                        orientation: $customer->orientation,
                        taskDate: $customer?->taskDate,
                    ),
                )
            );
        }
    }

    private function handleDispatchJobsAfterWorkshop(Collection $customers, ?int $idUserTo = null)
    {
        foreach ($customers as $customer) {
            dispatch(
                new SendRequestToCreateAuvoSuccessAssociateCustomerJob(
                    auvoDepartment: $this->auvoDepartment,
                    auvoCustomerDTO: new AuvoCustomerDTO(
                        externalId: $customer->external_id,
                        description: $customer->orientation,
                        name: $customer->name,
                        address: $customer->address,
                        manager: $this->auvoAccountDataEnvironment->manager,
                        note: $customer->orientation,

                    ),
                    auvoTaskDTO: new AuvoTaskDTO(
                        externalId: $customer->task_external_id,
                        idUserFrom: $this->auvoAccountDataEnvironment->idUserFrom,
                        idUserTo: $idUserTo,
                        orientation: $customer->orientation,
                        taskDate: $customer?->taskDate,
                        taskId: 192373
                    ),
                )
            );
        }
    }




    private function distributeDates(Collection $collection, array $dates)
    {
        // Conta o número total de itens na coleção
        $totalItems = $collection->count();

        // Calcula quantos itens devem receber cada data
        $chunkSize = ceil($totalItems / count($dates));

        // Divide a coleção em "pedaços" do tamanho necessário
        $chunks = $collection->chunk($chunkSize);

        $result = collect();

        // Itera sobre os pedaços e associa as datas
        foreach ($chunks as $index => $chunk) {
            $date = $dates[$index] ?? null; // Pega a data correspondente ou null
            $chunk = $chunk->map(function ($item) use ($date) {
                $item->taskDate = $date; // Adiciona a data ao item
                return $item;
            });

            $result = $result->merge($chunk); // Junta os pedaços de volta
        }

        return $result;
    }

    private function getRemainingWorkdays()
    {
        $today = now();
        $remainingDays = [];

        // Começa no próximo dia
        $currentDay = $today->copy();

        // Loop até sexta-feira
        while ($currentDay->dayOfWeek <= Carbon::FRIDAY) {
     
                $remainingDays[] = $currentDay->format('Y-m-d\TH:i:s'); // Formato ISO 8601
                $currentDay->addDay();
        }

        return $remainingDays;
    }

    private function getNextSevenDays()
    {
        $today = now();
        $days = [];

        $currentDay = $today->copy();

        for ($i = 0; $i < 7; $i++) {
            $days[] = $currentDay->format('Y-m-d\TH:i:s'); // Formato ISO 8601
            $currentDay->addDay();
        }

        return $days;
    }


    public function filterCollaboratorsById(array $items): array {
        $filteredItems = array_filter($items, function ($item) {
            return in_array($item['userID'], [194479]);
        });

        return $filteredItems;
    }

    private function generateCollection(array $items): Collection
    {
       $filteredItems = $this->filterCollaboratorsById($items);

        // Monta a Collection só com os filtrados
        $collection = new Collection();

        foreach ($filteredItems as $item) {
            $collection->push((object) $item);
        }

        return $collection;
    }

}
