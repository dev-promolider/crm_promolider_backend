<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPremioGanadoToDinamicaRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('dinamica_registros', function (Blueprint $table) {
            $table->string('premio_ganado')->nullable()->after('ha_ganado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('dinamica_registros', function (Blueprint $table) {
            $table->dropColumn('premio_ganado');
        });
    }
}
