<?php

namespace App\Services\MLM;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A que periodo pertenece un corte y cuando toca el siguiente.
 *
 * El plan que se le entrega al afiliado dice que el corte es "todos los dias 21 de
 * cada mes a las 12:00 PM (hora Lima, Peru)". Eso son tres datos —dia, hora y zona—
 * que hasta ahora no estaban en ninguna parte: el corte se lanzaba a mano cuando
 * alguien pulsaba el boton.
 *
 * El periodo es lo que hace que el corte no se pueda repetir. Con frecuencia mensual
 * hay un periodo por mes ("2026-09"); si el dia de manana se pasa a quincenal, dos
 * ("2026-09-Q1" y "2026-09-Q2"). El rango, en cambio, es siempre mensual, asi que su
 * bono usa siempre la clave del mes.
 */
class BinaryCutPeriodService
{
    public function __construct(private PlanSettings $settings)
    {
    }

    /**
     * Momento actual en la zona horaria configurada. Todo se calcula ahi: si el
     * servidor esta en UTC, un corte del dia 21 a las 12:00 de Lima cae el 21 a las
     * 17:00 UTC, y usar la hora del servidor lo correria de dia.
     */
    public function ahora(): Carbon
    {
        return Carbon::now($this->settings->zonaHoraria());
    }

    /**
     * Clave del periodo al que pertenece una fecha.
     */
    public function clavePeriodo(?Carbon $momento = null): string
    {
        $momento = ($momento ?? $this->ahora())->copy()->setTimezone($this->settings->zonaHoraria());

        if ($this->settings->frecuenciaCorte() === 'biweekly') {
            $quincena = $momento->day <= 15 ? 'Q1' : 'Q2';

            return $momento->format('Y-m') . '-' . $quincena;
        }

        return $momento->format('Y-m');
    }

    /**
     * Clave del mes. El rango y su bono son mensuales aunque el corte sea quincenal.
     */
    public function clavePeriodoMensual(?Carbon $momento = null): string
    {
        $momento = ($momento ?? $this->ahora())->copy()->setTimezone($this->settings->zonaHoraria());

        return $momento->format('Y-m');
    }

    /**
     * Momento en que el corte del periodo de una fecha queda habilitado.
     *
     * Mensual: el dia configurado, a la hora configurada.
     * Quincenal: el dia 15 para la primera quincena y el dia configurado para la
     * segunda, que es como queda la promesa del documento si algun dia se parte.
     */
    public function inicioDelPeriodo(?Carbon $momento = null): Carbon
    {
        $zona = $this->settings->zonaHoraria();
        $momento = ($momento ?? $this->ahora())->copy()->setTimezone($zona);
        [$hora, $minuto] = array_map('intval', explode(':', $this->settings->horaCorte()));

        if ($this->settings->frecuenciaCorte() === 'biweekly') {
            $dia = $momento->day <= 15 ? 15 : $this->settings->diaCorte();

            return $momento->copy()->setTime(0, 0)->day($dia)->setTime($hora, $minuto);
        }

        return $momento->copy()->setTime(0, 0)->day($this->settings->diaCorte())->setTime($hora, $minuto);
    }

    /**
     * Cuando se habilita el proximo corte que todavia no se ha ejecutado.
     */
    public function proximoCorte(): Carbon
    {
        $ahora = $this->ahora();
        $inicio = $this->inicioDelPeriodo($ahora);

        // Si el de este periodo aun no ha llegado, ese es el proximo.
        if ($ahora->lessThan($inicio) && !$this->periodoEjecutado($this->clavePeriodo($ahora))) {
            return $inicio;
        }

        // Si ya paso pero nadie lo ejecuto, sigue pendiente: es ahora mismo.
        if ($ahora->greaterThanOrEqualTo($inicio) && !$this->periodoEjecutado($this->clavePeriodo($ahora))) {
            return $inicio;
        }

        // Ya se ejecuto: hay que irse al periodo siguiente.
        $siguiente = $this->settings->frecuenciaCorte() === 'biweekly'
            ? ($ahora->day <= 15 ? $ahora->copy()->day(16) : $ahora->copy()->addMonthNoOverflow()->day(1))
            : $ahora->copy()->addMonthNoOverflow()->day(1);

        return $this->inicioDelPeriodo($siguiente);
    }

    /**
     * Si toca ejecutar el corte automatico: ya paso la hora del periodo y ese
     * periodo no se ha cortado todavia.
     *
     * Se comprueba contra el periodo, no contra el instante, para que un servidor
     * apagado a las 12:00 del dia 21 no se salte el corte del mes: en cuanto vuelva,
     * el periodo sigue sin ejecutar y el corte sale.
     */
    public function toca(): bool
    {
        if (!$this->settings->corteAutomatico()) {
            return false;
        }

        $ahora = $this->ahora();

        if ($ahora->lessThan($this->inicioDelPeriodo($ahora))) {
            return false;
        }

        return !$this->periodoEjecutado($this->clavePeriodo($ahora));
    }

    public function periodoEjecutado(string $clave): bool
    {
        return DB::table('binary_cut_runs')->where('period_key', $clave)->exists();
    }

    /**
     * Cortes ya ejecutados, del mas reciente al mas antiguo.
     */
    public function historial(int $limite = 12)
    {
        return DB::table('binary_cut_runs')
            ->orderByDesc('executed_at')
            ->orderByDesc('id')
            ->limit($limite)
            ->get();
    }
}
