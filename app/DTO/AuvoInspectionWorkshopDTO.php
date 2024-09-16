<?php

namespace App\DTO;

// 'id' => 2365,
// 'cnpj' => '47.172.095/0001-94',
// 'nome' => 'C.R.A CENTRO DE REPARAÇÃO AUTOMOTIVA - Mineiros/GO',
// 'endereco' => 'AVENIDA ERNESTO ELIAS DE REtodos os dias das 08:00 às 18:00ZENDE, QD 10 LT 10 - BOSQUE DOPS BURITIS, MINEIROS/GO - 75.835-173',
// 'horaInicio' => '15:00',
// 'diasSemana' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']

class AuvoInspectionWorkshopDTO
{
    public function __construct(
        public int $ilevaId,
        public string $cnpj,
        public string $address,
        public string $name,
        public string $visitTime,
        public array $daysOfWeek,
    ) {}
}
