<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:shift-attendance-from-shift-fixed')->monthlyOn(20, '03:00')->appendOutputTo(storage_path('logs/fixed_shifts.log'));
Schedule::command('app:shift-attendance-from-shift-rotating-command')->dailyAt('07:00')->appendOutputTo(storage_path('logs/rotating_shifts.log'));
Schedule::command('app:shift-attendance-from-shift-rotating-command')->dailyAt('13:00')->appendOutputTo(storage_path('logs/rotating_shifts.log'));
Schedule::command('app:shift-attendance-from-shift-rotating-command')->dailyAt('16:00')->appendOutputTo(storage_path('logs/rotating_shifts.log'));
Schedule::command('app:shift-attendance-from-shift-rotating-command')->dailyAt('21:00')->appendOutputTo(storage_path('logs/rotating_shifts.log'));

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();
