<?php

use App\Console\Commands\CompletePurchaseCommand;
use App\Console\Commands\DeleteOldMessages;
use App\Console\Commands\ReleasePurchasesCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks (moved from the old app/Console/Kernel.php)
|--------------------------------------------------------------------------
| The message-cleanup and release commands select records by age, so running
| them daily is equivalent to (and more reliable than) "every N days".
*/

// Delete messages older than marketplace.days_old_messages days.
Schedule::command(DeleteOldMessages::class, [config('marketplace.days_old_messages')])
    ->dailyAt('03:00');

// Release 'sent' purchases older than marketplace.days_old_purchases days.
Schedule::command(ReleasePurchasesCommand::class, [config('marketplace.days_old_purchases')])
    ->dailyAt('03:30');

// Complete purchases with enough balance every marketplace.days_complete days.
Schedule::command(CompletePurchaseCommand::class)
    ->cron('0 4 '.(config('marketplace.days_complete') > 1 ? '*/'.config('marketplace.days_complete') : '*').' * *');
