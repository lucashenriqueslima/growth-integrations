<?php

namespace App\DTO;

final class AuvoTaskDTO
{

    const LATITUDE = -23.558418;
    const LONGITUDE = -46.688081;

    public function __construct(
        public int $idUserFrom,
        public string $address,
        public string $orientation,
        public int $priority = 3,
        public ?string $externalId = null,
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
}
