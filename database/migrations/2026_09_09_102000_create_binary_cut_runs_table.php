<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una fila por corte binario ejecutado.
 *
 * El corte se podia lanzar tantas veces como se pulsara el boton, y ademas hay un
 * comando programado que lo dispara solo. Dos cortes en el mismo mes pagan dos veces
 * y asignan el rango dos veces, cuando el rango es mensual por definicion.
 *
 * La garantia no puede ser una comprobacion en PHP: dos peticiones a la vez se
 * cuelan igual. Es el indice unico sobre period_key el que lo impide, y como la fila
 * se inserta dentro de la misma transaccion que los pagos, si el corte falla no
 * queda rastro y se puede repetir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binary_cut_runs', function (Blueprint $table) {
            $table->id();
            // 2026-09 si el corte es mensual; 2026-09-Q1 y 2026-09-Q2 si es quincenal.
            // Un corte forzado por el administrador anade un sufijo (#2, #3...) para
            // que quede en el historial sin chocar con el indice unico.
            $table->string('period_key', 32)->unique();
            $table->unsignedInteger('batch');
            $table->string('frequency', 16)->default('monthly');
            $table->timestamp('executed_at')->nullable();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->unsignedInteger('users_paid')->default(0);
            $table->decimal('total_binary', 12, 2)->default(0);
            $table->decimal('total_generational', 12, 2)->default(0);
            $table->boolean('forced')->default(false);
            $table->timestamps();

            $table->index('batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binary_cut_runs');
    }
};
