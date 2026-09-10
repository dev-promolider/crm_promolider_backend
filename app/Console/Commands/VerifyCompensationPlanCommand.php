<?php

namespace App\Console\Commands;

use App\Services\MLM\PlanVerificationService;
use Illuminate\Console\Command;

/**
 * La verificacion del plan, desde la consola.
 *
 * Toda la comparacion vive en PlanVerificationService, que es el mismo que atiende
 * al panel: lo que se ve por pantalla y lo que responde la API salen del mismo sitio.
 *
 *   php artisan plan:verificar              solo informa
 *   php artisan plan:verificar --aplicar    escribe en la base los valores del documento
 */
class VerifyCompensationPlanCommand extends Command
{
    protected $signature = 'plan:verificar
                            {--aplicar : Escribir en la base los valores del documento}
                            {--seccion= : Revisar solo membresias, opc, corte, rangos o generacional}';

    protected $description = 'Compara la configuración del plan de compensación con el documento entregado al afiliado';

    public function handle(PlanVerificationService $verificacion)
    {
        $resultado = $verificacion->verificar($this->option('seccion'));

        $this->line('');
        $this->info('Plan de referencia: ' . $resultado['version']);
        $this->line('');

        if ($resultado['diferencias']) {
            $this->error($resultado['total'] . ' diferencias entre el documento y el sistema:');
            $this->line('');
            $this->table(
                ['Apartado', 'Concepto', 'Dice el documento', 'Tiene el sistema'],
                array_map('array_values', $resultado['diferencias'])
            );
        } else {
            $this->info('Todo lo comparable cuadra con el documento.');
        }

        if ($resultado['avisos']) {
            $this->line('');
            $this->warn('Avisos:');
            foreach ($resultado['avisos'] as $aviso) {
                $this->line('  · ' . $aviso);
            }
        }

        if (!$this->option('aplicar')) {
            if ($resultado['diferencias']) {
                $this->line('');
                $this->line('Para escribir los valores del documento en la base: php artisan plan:verificar --aplicar');
            }

            return $resultado['diferencias'] ? 1 : 0;
        }

        $pendientes = $verificacion->correccionesPendientes();

        if (!$pendientes) {
            return 0;
        }

        $this->line('');

        if (!$this->confirm("Se van a escribir {$pendientes} valores del documento en la base. Cambian lo que el sistema paga. ¿Continuar?", false)) {
            $this->warn('Cancelado. No se ha tocado nada.');

            return 1;
        }

        $this->info($verificacion->aplicar() . ' valores actualizados.');

        return 0;
    }
}
