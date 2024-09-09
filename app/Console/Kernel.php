<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Department;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //バックアップの処理と実行時間
        $schedule->command('backup:run --only-db')->dailyAt('02:00');
        $schedule->command('backup:run --only-db')->dailyAt('07:30');
        $schedule->command('backup:run --only-db')->dailyAt('12:30');
        $schedule->command('backup:run --only-db')->dailyAt('15:30');
        $schedule->command('backup:run --only-db')->dailyAt('20:30');

        //月が替わった時に印刷回数を1にリセット
        $schedule->command('print-count:update')->dailyAt('04:30');


    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
