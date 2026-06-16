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
        //
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
        $schedule->command('quizz:reset')->daily()->timezone('America/Lima');
        $schedule->command('membership:expiration')->daily()->timezone('America/Lima');
        $schedule->command('deliver:expansionBonus')->monthlyOn(1, '02:00')->timezone('America/Lima');

        if($schedule->command('db:backup')->dailyAt('23:52')->timezone('America/Lima')){
            $schedule->command('db:backupmail')->dailyAt('23:55')->timezone('America/Lima');
            $schedule->command('db:removebackup')->dailyAt('23:58')->timezone('America/Lima');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
