<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\DTO\AuvoCustomerDTO;
use App\DTO\AuvoTaskDTO;

class ProcessColaboradorTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AuvoTaskDTO $taskData;
    public function __construct(
        protected $colaborador,
        protected AuvoCustomerDTO $customer,
        protected array $oficina,
        protected string $accessToken,
        protected int $responseId,
        protected float $latitude,
        protected float $longitude
    ) {}

    public function handle(): void
    {
        $taskCount = 1; // Iniciar o contador

        for ($i = 0; $i < 60; $i++) {
            $currentDate = new \DateTime("now", new \DateTimeZone('America/Sao_Paulo'));
            $currentDate->modify("+{$i} days");
            $dayOfWeek = $currentDate->format('l');

            if (in_array($dayOfWeek, $this->oficina['diasSemana'])) {
                $taskDate = $this->getDateTimeForDay($currentDate, $this->oficina['horaInicio']);
                $existingTask = Task::where('auvo_id_task', $this->customer->externalId)->first();

                if ($existingTask) {
                    // Log::info("Task for customer {$this->customer->id} already exists with ID {$existingTask->auvo_id_task}.");
                    continue;
                }

                $this->taskData = new AuvoTaskDTO(
                    externalId: "{$this->customer->externalId}_{$taskCount}",
                    taskType: 153103,
                    idUserFrom: 163489,
                    idUserTo: $this->colaborador['id'],
                    taskDate: $taskDate,
                    address: $this->customer->address,
                    orientation: $this->customer->orientation,
                    priority: 3,
                    questionnaireId: 173499,
                    customerId: $this->responseId,
                    checkinType: 1

                );

                dispatch(new GeneratePdfAuvoJob($this->customer, $this->taskData, $this->accessToken));

                $taskCount++;
            }
        }
    }

    private function getDateTimeForDay(\DateTime $date, string $horaInicio): string
    {
        list($hours, $minutes) = explode(':', $horaInicio);
        $date->setTime((int)$hours, (int)$minutes);
        return $date->format('Y-m-d\TH:i:s');
    }
}
