<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('system:scheduler-heartbeat')
    ->everyMinute()
    ->timezone('Asia/Karachi')
    ->withoutOverlapping(2);

Schedule::command('kes:daily-backup')
    ->dailyAt('02:00')
    ->timezone('Asia/Karachi')
    ->withoutOverlapping();

Schedule::command('tasks:send-reminders')
    ->everyMinute()
    ->withoutOverlapping(5);

Schedule::command(
    'passport:send-expiry-alerts',
)
    ->dailyAt('08:00')
    ->timezone('Asia/Karachi')
    ->withoutOverlapping(30);

Schedule::command('system:maintain-errors')
    ->dailyAt('03:30')
    ->timezone('Asia/Karachi')
    ->withoutOverlapping(30);
