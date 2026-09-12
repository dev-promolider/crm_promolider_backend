<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columnas que faltaban para poder administrar los rangos de verdad.
 *
 * El equipo quiere poder crear rangos, quitarlos, renombrarlos y reordenarlos desde
 * el panel. Con la tabla como estaba eso no se podia:
 *
 *   - El orden salia de vol_min y, en empate, del id. Hoy hay tres rangos con
 *     vol_min 89, asi que el orden dependia del id y un rango nuevo intercalado
 *     caia en el sitio equivocado.
 *   - No habia forma de retirar un rango: rank_binary y binary_cut_histories
 *     apuntan a rank_bonus con clave foranea, asi que borrar uno con historial es
 *     imposible. Con status se retira sin perder el historial.
 *   - El plan promete el bono mensual de rango tras mantenerlo tres meses seguidos,
 *     y en el rango mas alto lo promete trimestral. Eso son dos parametros, no una
 *     constante escondida en el codigo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rank_bonus', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('name');
            $table->boolean('status')->default(true)->after('icon');
            $table->unsignedSmallInteger('monthly_bonus_months')->default(3)->after('monthly_bonus');
            $table->string('monthly_bonus_frequency', 10)->default('monthly')->after('monthly_bonus_months');
        });

        // El orden de hoy es el que ya se ve en el panel: por id. Se deja en saltos
        // de diez para poder intercalar un rango sin renumerar los demas.
        $posicion = 10;

        foreach (DB::table('rank_bonus')->orderBy('id')->pluck('id') as $id) {
            DB::table('rank_bonus')->where('id', $id)->update(['sort_order' => $posicion]);
            $posicion += 10;
        }
    }

    public function down(): void
    {
        Schema::table('rank_bonus', function (Blueprint $table) {
            $table->dropColumn([
                'sort_order',
                'status',
                'monthly_bonus_months',
                'monthly_bonus_frequency',
            ]);
        });
    }
};
