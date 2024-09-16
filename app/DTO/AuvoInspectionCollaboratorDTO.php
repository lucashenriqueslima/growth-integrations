<?php

namespace App\DTO;

class AuvoInspectionCollaboratorDTO
{
    public function __construct(
        public string $auvoId,
        public string $name,
        public array $workshops,
    ) {}
}
