<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('auvo:tracking-update')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('06:00')
    ->dailyAt('13:00');

Schedule::command('auvo:inspection-update')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('02:00');

Schedule::command('auvo:expertise-update')
    ->everyFourHours();

Schedule::command('auvo:associate-success-update')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('00:00');
