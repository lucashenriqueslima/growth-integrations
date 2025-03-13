<?php

namespace App\Jobs\Inspection;

use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;
use App\Enums\AuvoDepartment;
use App\Models\AuvoCustomer;
use App\Models\AuvoTask;
use App\Traits\AuvoIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Laravel\Octane\Facades\Octane;

class SendRequestToCreateAuvoInspectionCustomer implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Queueable, SerializesModels, AuvoIntegration;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly AuvoDepartment $auvoDepartment,
        public readonly AuvoCustomerDTO $auvoCustomerDTO,
        public AuvoTaskDTO $auvoTaskDTO,
        public readonly string $startDate,
        public readonly ?array $workshop = null,
    ) {}


    /**
     * Execute the job.
     */
    public function handle(PendingRequest $rawClient): void
    {
        try {

            $auvoCustomerId = AuvoCustomer::where('external_id', $this->auvoCustomerDTO->externalId)
                ->where('auvo_department', $this->auvoDepartment->value)
                ->first()?->customer_id;

            if ($auvoCustomerId) {
                $this->auvoCustomerDTO->customerId = $auvoCustomerId;
                $this->auvoTaskDTO->customerId = $auvoCustomerId;
            } else {
                $response = $this->sendRequestToCreateOrUpdateCustomer();

                $this->auvoCustomerDTO->customerId = $response->json()['result']['id'];
                $this->auvoTaskDTO->customerId = $response->json()['result']['id'];
            }


            $customer = $this->updateOrCreateCustomer();

            // if (!$this->workshop) {
            return;
            // }

            $this->auvoTaskDTO->auvoCostumerId = $customer->id;

            $specificDays = $this->getSpecificDays($this->startDate, $this->workshop['days_of_week'], $this->workshop['visit_time']);

            $specificDays->each(function ($specificDay) use (&$tasks) {

                $sufixDate = $specificDay->format('Ymd');

                $this->auvoTaskDTO->externalId = "{$this->auvoTaskDTO->externalId}{$sufixDate}";

                $auvoTaskId = AuvoTask::where('external_id', $this->auvoTaskDTO->externalId)
                    ->where('auvo_department', $this->auvoDepartment->value)
                    ->first()?->task_id;

                if ($auvoTaskId) {
                    return;
                }

                $this->auvoTaskDTO->taskDate = $specificDay->format('Y-m-d\TH:i:s');



                // dispatch(
                //     new SendRequestToCreateAuvoInspectionTask(
                //         $this->auvoDepartment,
                //         $this->auvoCustomerDTO,
                //         $this->auvoTaskDTO,
                //         $sufixDate,

                //     )
                // );
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function getSpecificDays(string $startDate, array $daysOfWeek, string $visitTime): Collection
    {
        $start = Carbon::parse($startDate)->setTimeFromTimeString("{$visitTime}:01");
        $end = $start->copy()->addDays(60);

        $compareDate = Carbon::now()->hour(0)->minute(0)->second(0);


        $dates = new Collection();

        //writeln
        $startOfNextWeek = Carbon::now()->startOfWeek();
        $endOfNextWeek = Carbon::now()->endOfWeek();


        //verify if it is in this week
        while ($start->lte($end)) {
            if (
                in_array($start->dayOfWeek, $daysOfWeek) &&
                $start->gte($compareDate) &&
                $start->between($startOfNextWeek, $endOfNextWeek)
            ) {
                $dates->push($start->copy());
            }

            $start->addDay();
        }

        return $dates;
    }
}
