<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ata cada fila de generational_bonuses a su rango por identificador propio.
 *
 * Hasta ahora las dos tablas se cruzaban de dos maneras, y ninguna aguanta que el
 * administrador toque los rangos:
 *
 *   - El panel cruzaba generational_bonuses.id = rank_bonus.id. Los identificadores
 *     no se corresponden (el 1 de una tabla es Mentor y el de la otra Aprendiz), asi
 *     que el administrador veia los porcentajes corridos un rango.
 *   - El corte cruzaba por nombre, que arregla lo anterior pero se rompe en cuanto
 *     alguien renombra un rango desde el panel.
 *
 * Con una columna propia el nombre pasa a ser solo una etiqueta: se puede cambiar
 * cuantas veces haga falta sin que se mueva ni un porcentaje.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generational_bonuses', function (Blueprint $table) {
            $table->unsignedBigInteger('rank_bonus_id')->nullable()->after('id');
        });

        // El emparejamiento inicial es por nombre porque es el unico dato comun que
        // hay hoy. Se hace una sola vez, aqui, y a partir de este momento manda la
        // columna nueva.
        $ranks = DB::table('rank_bonus')->get(['id', 'name']);
        $porNombre = [];

        foreach ($ranks as $rank) {
            $porNombre[$this->normalizar($rank->name)] = (int) $rank->id;
        }

        $huerfanas = [];

        foreach (DB::table('generational_bonuses')->get(['id', 'range_name']) as $fila) {
            $rankId = $porNombre[$this->normalizar($fila->range_name)] ?? null;

            if ($rankId === null) {
                $huerfanas[] = $fila->range_name;
                continue;
            }

            DB::table('generational_bonuses')
                ->where('id', $fila->id)
                ->update(['rank_bonus_id' => $rankId]);
        }

        if ($huerfanas) {
            // No se borra nada: quedan con rank_bonus_id nulo y a la vista para que
            // el administrador decida. El corte simplemente no las usa.
            logger()->warning('[MIGRACION] Filas de generational_bonuses sin rango equivalente', [
                'nombres' => $huerfanas,
            ]);
        }

        // Todo rango tiene que poder configurarse, incluidos los que hoy no tienen
        // fila (Aprendiz y Rector Presidente Crown). Se crean en cero: los valores
        // del plan los escribe "php artisan plan:verificar --aplicar", que es una
        // decision de negocio y no debe esconderse dentro de una migracion.
        $conFila = DB::table('generational_bonuses')
            ->whereNotNull('rank_bonus_id')
            ->pluck('rank_bonus_id')
            ->all();

        foreach ($ranks as $rank) {
            if (in_array((int) $rank->id, $conFila, true)) {
                continue;
            }

            DB::table('generational_bonuses')->insert([
                'rank_bonus_id' => $rank->id,
                'range_name'    => $rank->name,
                'g_1' => 0, 'g_2' => 0, 'g_3' => 0, 'g_4' => 0,
                'g_5' => 0, 'g_6' => 0, 'g_7' => 0, 'g_8' => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        Schema::table('generational_bonuses', function (Blueprint $table) {
            $table->unique('rank_bonus_id', 'generational_bonuses_rank_bonus_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('generational_bonuses', function (Blueprint $table) {
            $table->dropUnique('generational_bonuses_rank_bonus_id_unique');
            $table->dropColumn('rank_bonus_id');
        });
    }

    /**
     * Compara nombres sin acentos, sin dobles espacios y sin distinguir mayusculas:
     * "Vice Rector" y "vicerrector" no deben quedar separados por un detalle asi.
     */
    private function normalizar(?string $nombre): string
    {
        $limpio = mb_strtolower(trim((string) $nombre));
        $limpio = strtr($limpio, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return preg_replace('/\s+/', '', $limpio) ?? $limpio;
    }
};
