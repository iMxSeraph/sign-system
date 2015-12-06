<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\TransferData::class,
        \App\Console\Commands\SendEmail::class,
        \App\Console\Commands\ResetData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('routine:transfer-data')->everyMinute();
        $schedule->command('routine:send-email')->dailyAt('11:00');
        $schedule->command('routine:send-email')->dailyAt('17:00');
    }
}
