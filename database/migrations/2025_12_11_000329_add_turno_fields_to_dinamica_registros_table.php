<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTurnoFieldsToDinamicaRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dinamica_registros', function (Blueprint $table) {
            $table->integer('turno')->nullable()->after('email');
            $table->boolean('ha_jugado')->default(false)->after('turno');
            $table->boolean('ha_ganado')->default(false)->after('ha_jugado');
            $table->timestamp('turno_inicio')->nullable()->after('ha_ganado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dinamica_registros', function (Blueprint $table) {
            $table->dropColumn(['turno', 'ha_jugado', 'ha_ganado', 'turno_inicio']);
        });
    }
}
