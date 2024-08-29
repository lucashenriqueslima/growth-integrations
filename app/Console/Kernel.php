<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\HandleAuvoUpdatesForExpertiseAccountCommand::class,
        \App\Console\Commands\HandleAuvoUpdatesForInspectionAccountCommand::class,
        \App\Console\Commands\CountData::class,
    ];

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
