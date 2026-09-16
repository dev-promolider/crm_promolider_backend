<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Antiguedad para subir de rango.
 *
 * El ingeniero lo dejo dicho asi: para subir de rango hay que haber mantenido el
 * anterior una cantidad de meses, y esa cantidad la decide el administrador rango a
 * rango, desde 0 hasta lo que quiera. Los primeros rangos no la exigen y los mas
 * altos si.
 *
 * Entra en cero en todos los rangos a proposito: nadie cambia de rango por desplegar
 * esto, y los meses se empiezan a contar desde los cortes del sistema nuevo. Poner
 * cifras de negocio dentro de una migracion no corresponde; se ponen desde el panel.
 *
 * La tabla rank_bonus la sigue leyendo el monolito (promolider.info), que usa la
 * misma base, asi que solo se anade una columna y no se toca ninguna existente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rank_bonus', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_months_previous_rank')->default(0)->after('active_direct');
        });
    }

    public function down(): void
    {
        Schema::table('rank_bonus', function (Blueprint $table) {
            $table->dropColumn('min_months_previous_rank');
        });
    }
};
