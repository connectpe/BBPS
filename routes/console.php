<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('payin:auto-settlement')
    ->dailyAt('13:00');

Schedule::command('app:update-payin-wallet')
    ->everyMinute();   