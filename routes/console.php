<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('auvo:tracking-update')
    ->everyThreeHours();

Schedule::command('auvo:inspection-update')
    ->timezone('America/Sao_Paulo')
    ->dailyAt('08:00');

Schedule::command('auvo:expertise-update')
    ->hourly();
