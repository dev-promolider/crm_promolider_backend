<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Porcentajes generacionales en filas, no en columnas.
 *
 * generational_bonuses tiene una columna por generacion (g_1 a g_8), asi que el
 * plan no podia pasar de ocho. El ingeniero quiere que la tabla crezca en las dos
 * direcciones: mas rangos hacia abajo y mas generaciones hacia la derecha. Con una
 * fila por rango y generacion no hay limite.
 *
 * generational_bonuses no se borra: la sigue leyendo el monolito, que comparte la
 * base. El panel escribe en las dos mientras la generacion quepa en g_1..g_8.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_generation_percentages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rank_bonus_id');
            $table->unsignedSmallInteger('generation');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['rank_bonus_id', 'generation'], 'rank_generation_unique');
            $table->index('rank_bonus_id');
        });

        $ahora = now();

        foreach (DB::table('generational_bonuses')->whereNotNull('rank_bonus_id')->get() as $fila) {
            for ($g = 1; $g <= 8; $g++) {
                $valor = (float) ($fila->{'g_' . $g} ?? 0);

                if ($valor <= 0) {
                    continue;
                }

                DB::table('rank_generation_percentages')->insert([
                    'rank_bonus_id' => $fila->rank_bonus_id,
                    'generation'    => $g,
                    'percentage'    => $valor,
                    'created_at'    => $ahora,
                    'updated_at'    => $ahora,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_generation_percentages');
    }
};
