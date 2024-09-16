<?php

namespace App\DTO;

use App\Enums\AuvoDepartment;

final class AuvoTaskDTO
{

    const LATITUDE = -23.558418;
    const LONGITUDE = -46.688081;

    public function __construct(
        public string $externalId,
        public ?int $idUserFrom = null,
        public ?string $address = null,
        public ?string $orientation = null,
        public ?int $auvoCostumerId = null,
        public ?int $taskId = null,
        public ?int $priority = 3,
        public ?int $idUserTo = null,
        public ?int $teamId = null,
        public ?string $taskDate = null,
        public ?int $questionnaireId = null,
        public ?int $customerId = null,
        public ?bool $sendSatisfactionSurvey = null,
        public ?array $attachments = null,
        public ?array $keyWords = null,
        public ?int $taskType = null,
    ) {}

    public function toArray(): array
    {
        return
            array_filter(
                [
                    'externalId' => $this->externalId,
                    'idUserFrom' => $this->idUserFrom,
                    'idUserTo' => $this->idUserTo,
                    'teamId' => $this->teamId,
                    'taskDate' => $this->taskDate,
                    'latitude' => self::LATITUDE,
                    'longitude' => self::LONGITUDE,
                    'address' => $this->address,
                    'orientation' => $this->orientation,
                    'priority' => $this->priority,
                    'questionnaireId' => $this->questionnaireId,
                    'customerId' => $this->customerId,
                    'sendSatisfactionSurvey' => $this->sendSatisfactionSurvey,
                    'attachments' => $this->attachments,
                    'keyWords' => $this->keyWords,
                    'taskType' => $this->taskType,
                    'customerExternalId' => $this->externalId,
                    'checkInType' => 1,
                ],
                fn($value) => $value !== null
            );
    }

    //to array to DB insert
    public function toArrayDB(AuvoDepartment $auvoDepartment): array
    {
        return
            array_filter(
                [
                    'auvo_department' => $auvoDepartment,
                    'external_id' => $this->externalId,
                    'task_id' => $this->taskId,
                    'auvo_customer_id' => $this->auvoCostumerId,
                ],
                fn($value) => $value !== null
            );
    }
}
