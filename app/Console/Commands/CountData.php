<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count data every minute.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = DB::connection('ileva')->select(
            "
            SELECT
            COUNT(*) AS total
            FROM
                hbrd_asc_beneficio_veiculo hab
            WHERE
                hab.id_beneficio IN (118,122,130,174,234,181,123,119,31);
            "
        );

        $totalData = $result[0]->total ?? 0;

        $this->info("Total: " . $totalData);
        $this->logExecution();
    }

    protected function logExecution()
    {
        $timestamp = now()->toDateTimeString();
        Log::channel('command_times')->info("Command 'count-data' was executed at {$timestamp}");
    }
}
