<?php

namespace App\Services\MLM;

use Illuminate\Support\Facades\DB;

/**
 * Que rango tuvo cada afiliado en cada mes, segun los cortes ya ejecutados.
 *
 * Dos cosas distintas hacen esa misma pregunta:
 *
 *   - El bono de estabilidad, que exige mantener el mismo rango unos meses seguidos.
 *   - La antiguedad para subir de rango, que exige haber tenido el rango anterior
 *     (o uno superior) unos meses seguidos antes de poder subir a este.
 *
 * Tener dos respuestas para la misma pregunta era pedir que con el tiempo dijeran
 * cosas distintas, asi que la respuesta vive aqui y las dos la piden.
 *
 * Solo cuentan los cortes registrados en binary_cut_runs: los lotes historicos del
 * monolito no tienen fila ahi, asi que nadie arrastra una racha inventada de antes.
 */
class RankHistoryService
{
    /**
     * El historial completo: el rango de cada afiliado en cada mes y el ultimo lote
     * de cada mes.
     *
     * No se guarda en memoria a proposito. Se le llama una vez por proceso (el corte,
     * la entrega del bono) y entre dos llamadas puede haber entrado un corte nuevo:
     * devolver lo de antes seria contar mal.
     *
     * @return array{rangos: array<string, array<int, int>>, lotes: array<string, int>}
     */
    public function historial(): array
    {
        $lotesPorMes = [];

        foreach (DB::table('binary_cut_runs')->whereNotNull('executed_at')->get() as $run) {
            // 2026-09-Q2 y 2026-09 caen los dos en el mes 2026-09: el rango es mensual.
            $mes = substr($run->period_key, 0, 7);
            $lotesPorMes[$mes][] = (int) $run->batch;
        }

        if (!$lotesPorMes) {
            return ['rangos' => [], 'lotes' => []];
        }

        $filas = DB::table('rank_binary')
            ->whereIn('batch', array_merge(...array_values($lotesPorMes)))
            ->orderBy('batch')
            ->get(['user_id', 'rank_id', 'batch']);

        $porLote = [];

        foreach ($filas as $fila) {
            // Si el mes tuvo dos cortes, manda el ultimo: es el rango con el que el
            // afiliado termina el mes, que es lo que promete el plan.
            $porLote[(int) $fila->batch][(int) $fila->user_id] = (int) $fila->rank_id;
        }

        $rangos = [];
        $lotes = [];

        ksort($lotesPorMes);

        foreach ($lotesPorMes as $mes => $lotesDelMes) {
            sort($lotesDelMes);
            $rangos[$mes] = [];
            $lotes[$mes] = (int) end($lotesDelMes);

            foreach ($lotesDelMes as $lote) {
                foreach ($porLote[$lote] ?? [] as $userId => $rankId) {
                    $rangos[$mes][$userId] = $rankId;
                }
            }
        }

        return ['rangos' => $rangos, 'lotes' => $lotes];
    }

    /**
     * Meses seguidos, hacia atras desde el mes dado incluido, en los que el afiliado
     * tuvo un rango que cuenta.
     *
     * Un mes sin rango asignado corta la racha: si ese mes no califico o no tuvo
     * puntos en las dos piernas, no mantuvo ningun rango.
     *
     * @param array<string, array<int, int>> $rangosPorMes  mes => [user_id => rank_id]
     * @param callable                       $cuenta        recibe el rank_id del mes
     */
    public function racha(array $rangosPorMes, string $desde, int $userId, callable $cuenta): int
    {
        $meses = array_keys($rangosPorMes);
        sort($meses);

        $indice = array_search($desde, $meses, true);

        if ($indice === false) {
            return 0;
        }

        $racha = 0;

        for ($i = $indice; $i >= 0; $i--) {
            $rango = $rangosPorMes[$meses[$i]][$userId] ?? null;

            if ($rango === null || !$cuenta((int) $rango)) {
                break;
            }

            $racha++;
        }

        return $racha;
    }

    /**
     * El ultimo mes con corte de los que se le pasen, o null si no hay ninguno.
     *
     * @param array<string, array<int, int>> $rangosPorMes
     */
    public function ultimoMes(array $rangosPorMes): ?string
    {
        $meses = array_keys($rangosPorMes);
        sort($meses);

        return $meses ? (string) end($meses) : null;
    }
}
