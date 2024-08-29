<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('auvo-customer-update')
    ->dailyAt('07:00')
    ->timezone('America/Sao_Paulo');

Schedule::command('field-control-customer-update')
    ->everyThirtyMinutes()
    ->timezone('America/Sao_Paulo');

Schedule::command('count-data')
    ->everyMinute()
    ->timezone('America/Sao_Paulo');
