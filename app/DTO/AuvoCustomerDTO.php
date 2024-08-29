<?php

namespace App\DTO;

final class AuvoCustomerDTO
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $description,
        public readonly string $name,
        public readonly string $address,
        public readonly string $manager = 'thais santos',
        public readonly string $note,
        public readonly bool $active = true,
        public readonly ?int $workshopId = null,
        public readonly ?string $cpfCnpj = null,
        public readonly ?string $email = null,
        public readonly ?array $phoneNumber = null,
        public readonly ?string $orientation = null,
        public readonly ?array $groupsId = null,
        public readonly ?array $order_items = [],
        public readonly ?array $order_summary = [],
    ) {}

    public function toArray(): array
    {
        return [
            'externalId' => $this->externalId,
            'description' => $this->description,
            'name' => $this->name,
            'address' => $this->address,
            'manager' => $this->manager,
            'note' => $this->note,
            'active' => $this->active,
            'workshopId' => $this->workshopId,
            'cpfCnpj' => $this->cpfCnpj,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'orientation' => $this->orientation,
            'groupsId' => $this->groupsId,
        ];
    }
}
