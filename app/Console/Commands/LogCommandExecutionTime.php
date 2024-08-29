<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LogCommandExecutionTime extends Command
{
    protected $signature = 'log:command-time {commandName}';
    protected $description = 'Log the execution time of a command';

    public function handle()
    {
        $commandName = $this->argument('commandName');
        $timestamp = now()->toDateTimeString();
        Log::channel('command_times')->info("Command '{$commandName}' was executed at {$timestamp}");
    }
}
