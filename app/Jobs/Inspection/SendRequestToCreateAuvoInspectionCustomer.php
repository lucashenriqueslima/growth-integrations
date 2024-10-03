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
        protected readonly AuvoDepartment $auvoDepartment,
        protected readonly AuvoCustomerDTO $auvoCustomerDTO,
        protected readonly AuvoTaskDTO $auvoTaskDTO,
        protected readonly string $startDate,
        protected readonly ?array $workshop = null,
    ) {}


    /**
     * Execute the job.
     */
    public function handle(PendingRequest $rawClient): void
    {
        try {
            $response = $this->sendRequestToCreateOrUpdateCustomer();

            $this->auvoCustomerDTO->customerId = $response->json()['result']['id'];
            $this->auvoTaskDTO->customerId = $response->json()['result']['id'];

            $customer = $this->updateOrCreateCustomer();

            if (!$this->workshop) {
                return;
            }

            $this->auvoTaskDTO->auvoCostumerId = $customer->id;

            $specificDays = $this->getSpecificDays($this->startDate, $this->workshop['days_of_week'], $this->workshop['visit_time']);



            $specificDays->each(function ($specificDay) use (&$tasks) {

                $sufixDate = $specificDay->format('Ymd');

                if (
                    AuvoTask::where('external_id', "{$this->auvoCustomerDTO->externalId}{$sufixDate}")
                    ->where('auvo_department', $this->auvoDepartment->value)
                    ->exists()
                ) {
                    return;
                }

                $this->auvoTaskDTO->taskDate = $specificDay->format('Y-m-d\TH:i:s');

                $this->auvoTaskDTO->externalId = "{$this->auvoCustomerDTO->externalId}{$sufixDate}";


                dispatch(
                    new SendRequestToCreateAuvoInspectionTask(
                        $this->auvoDepartment,
                        $this->auvoCustomerDTO,
                        $this->auvoTaskDTO,
                    )
                );
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

        while ($start->lte($end)) {
            if (in_array($start->dayOfWeek, $daysOfWeek) && $start->gte($compareDate)) {
                $dates->push($start->copy());
            }

            $start->addDay();
        }

        return $dates;
    }
}
