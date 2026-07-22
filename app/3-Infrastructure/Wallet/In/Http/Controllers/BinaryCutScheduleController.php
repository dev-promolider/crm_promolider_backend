<?php

namespace Promolider\Infrastructure\Wallet\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Promolider\Application\Wallet\UseCases\BinaryCut\ScheduleBinaryCutUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\GetBinaryCutScheduleUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\CancelBinaryCutScheduleUseCase;
use Promolider\Application\Wallet\UseCases\BinaryCut\ExecuteBinaryCutUseCase;

class BinaryCutScheduleController extends Controller
{
    public function __construct(
        private ScheduleBinaryCutUseCase $scheduleBinaryCutUseCase,
        private GetBinaryCutScheduleUseCase $getBinaryCutScheduleUseCase,
        private CancelBinaryCutScheduleUseCase $cancelBinaryCutScheduleUseCase,
        private ExecuteBinaryCutUseCase $executeBinaryCutUseCase
    ) {}

    public function getSchedule()
    {
        $datetime = $this->getBinaryCutScheduleUseCase->execute();
        return response()->json(['scheduled_at' => $datetime]);
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'datetime' => 'required|date_format:Y-m-d H:i:s'
        ]);

        $this->scheduleBinaryCutUseCase->execute($request->datetime);
        return response()->json(['message' => 'Corte binario programado con éxito.', 'scheduled_at' => $request->datetime]);
    }

    public function cancel()
    {
        $this->cancelBinaryCutScheduleUseCase->execute();
        return response()->json(['message' => 'Programación de corte binario cancelada.']);
    }

    public function executeNow()
    {
        try {
            $this->executeBinaryCutUseCase->execute();
            return response()->json(['message' => 'Corte binario ejecutado con éxito.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al ejecutar el corte: ' . $e->getMessage()], 500);
        }
    }
}
