<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // El corte binario. Corre cada minuto y él mismo decide si toca: mira el
        // calendario configurado (por defecto el día 21 a las 12:00 de Lima, que es
        // lo que promete el plan) y la programación puntual del panel. Si el periodo
        // ya está cortado no hace nada, así que no importa cuántas veces se lance.
        $schedule->command('binarycut:process')->everyMinute()->withoutOverlapping();

        // Bono mensual de rango. Va por su cuenta y siempre una vez al mes, porque
        // el rango es mensual aunque el corte llegue a ser quincenal. El día 1 se
        // premia el mes que acaba de cerrarse.
        $schedule->command('mlm:bono-rango')
            ->monthlyOn(1, '03:00')
            ->timezone('America/Lima')
            ->withoutOverlapping();

        // Bono de expansión: el día 1 de cada mes, como en el sistema anterior.
        $schedule->command('deliver:expansion-bonus')
            ->monthlyOn(1, '02:00')
            ->timezone('America/Lima')
            ->withoutOverlapping();
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
