<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CreateTasksAuvoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Collection $colaboradores,
        protected $customer,
        protected int $idOficina,
        protected string $accessToken,
        protected int $responseId,
        protected float $latitude,
        protected float $longitude
    ) {}

    public function handle(): void
    {

        $oficina = $this->getOficinaById($this->idOficina);
        if (!$oficina) {
            Log::error("Oficina with ID {$this->idOficina} not found.");
            return;
        }

        foreach ($this->colaboradores as $colaborador) {
            if (!isset($colaborador['id'])) {
                Log::error("Colaborador ID is not set.");
                continue;
            }

            if (!$this->isColaboradorResponsavelPelaOficina($colaborador, $this->idOficina)) {
                // Log::info("Colaborador {$colaborador['id']} não é responsável pela oficina {$this->idOficina}.");
                continue;
            }

            dispatch(new ProcessColaboradorTasksJob($colaborador, $this->customer, $oficina, $this->accessToken, $this->responseId, $this->latitude, $this->longitude));
        }
    }

    private function getOficinaById(int $idOficina): ?array
    {
        foreach ($this->colaboradores as $colaborador) {
            if (isset($colaborador['ids_oficina'])) {
                foreach ($colaborador['ids_oficina'] as $oficina) {
                    if (isset($oficina['id']) && $oficina['id'] == $idOficina) {
                        return $oficina;
                    }
                }
            }
        }
        return null;
    }

    private function isColaboradorResponsavelPelaOficina($colaborador, $idOficina): bool
    {
        if (isset($colaborador['ids_oficina'])) {
            foreach ($colaborador['ids_oficina'] as $oficina) {
                if (isset($oficina['id']) && $oficina['id'] == $idOficina) {
                    return true;
                }
            }
        }
        return false;
    }
}
