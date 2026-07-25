<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Promolider\Application\Wallet\UseCases\BinaryCut\GetBinaryCutScheduleUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\ExecuteBinaryCutUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\CancelBinaryCutScheduleUseCase;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessBinaryCutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binarycut:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled binary cuts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(
        GetBinaryCutScheduleUseCase $getSchedule,
        ExecuteBinaryCutUseCase $executeCut,
        CancelBinaryCutScheduleUseCase $cancelSchedule
    ) {
        $scheduledAt = $getSchedule->execute();

        if (!$scheduledAt) {
            return 0; // Nothing scheduled
        }

        $scheduledTime = Carbon::parse($scheduledAt);

        if (Carbon::now()->greaterThanOrEqualTo($scheduledTime)) {
            Log::info("ProcessBinaryCutCommand: Iniciando corte binario programado para {$scheduledAt}");
            try {
                $executeCut->execute();
                $cancelSchedule->execute(); // Clear schedule after successful execution
                Log::info("ProcessBinaryCutCommand: Corte binario completado exitosamente.");
                $this->info("Corte binario ejecutado y programación limpiada.");
            } catch (\Exception $e) {
                Log::error("ProcessBinaryCutCommand: Error ejecutando el corte: " . $e->getMessage());
                $this->error("Falló la ejecución del corte binario.");
                return 1;
            }
        }

        return 0;
    }
}
