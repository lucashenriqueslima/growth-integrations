<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpdateFieldControlCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $customer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PendingRequest $client): void
    {
        $client = $client->withHeaders([
            "Content-Type" => "application/json",
            "X-Api-Key" => env('FIELD_CONTROL_API_KEY')
        ]);

        try {
            $response = $client->get('https://carchost.fieldcontrol.com.br/customers?q=code:"' . $this->customer->code . '"');


            if (!in_array($response->status(), [200, 201])) {
                Log::error("Error fetching customer {$this->customer->name}:  {$response->body()}");
            }

            $items = $response->json()['items'];

            foreach ($items as $item) {
                if ($item['code'] == $this->customer->code && $item['archived'] == false) {
                    Log::info("Customer {$this->customer->name} already exists in Field Control");
                    return;
                }
            }

            sleep(1);

            try {
                $response = $client->post(
                    'https://carchost.fieldcontrol.com.br/customers',
                    [
                        'name' => $this->customer->name,
                        'documentNumber' => $this->customer->cpf,
                        'code' => (string) $this->customer->code,
                        'external' => [
                            'id' => (string) $this->customer->code
                        ],
                        'address' => [
                            'zipCode' => Str::remove('-', $this->customer->cep,),
                            'street' => $this->customer->logradouro,
                            'number' => $this->customer->numero ?? 'S/N',
                            'neighborhood' => $this->customer->bairro,
                            'complement' => $this->customer->complemento,
                            'city' => $this->customer->cidade,
                            'state' => $this->customer->uf,
                            'coords' => [
                                "latitude" => -23.558418,
                                "longitude" => -46.688081
                            ]
                        ]

                    ]
                );


                if (!in_array($response->status(), [200, 201])) {
                    Log::error("Error updating customer {$this->customer->name}:  {$response->body()}");
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
