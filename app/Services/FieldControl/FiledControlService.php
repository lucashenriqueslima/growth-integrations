<?php

namespace App\Services\FieldControl;

use App\Jobs\UpdateFieldControlCustomerJob;
use App\Models\Ileva\IlevaAssociateVehicle;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;


class FieldControlService
{
    private PendingRequest $client;
    public function __construct()
    {
        $this->client = Http::baseUrl(env('FIELD_CONTROL_API_URL'))
            ->withHeaders([
                "Content-Type" => "application/json",
                "X-Api-Key" => env('FIELD_CONTROL_API_KEY')
            ]);
    }

    public static function updateCustomers(array $customers): void
    {
        foreach ($customers as $customer) {
            UpdateFieldControlCustomerJob::dispatch($customer);
        }
    }

    public static function getIlevaCustomers(): array
    {
        return IlevaAssociateVehicle::getVehiclesForFieldControl();
    }
}
