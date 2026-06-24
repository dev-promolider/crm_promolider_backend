<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMasterclassUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masterclass_user', function (Blueprint $table){
            $table->dropColumn(['nationality','age']);

            $table->string('country')->after('phone');
            $table->date('birthdate')->after('country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('masterclass_user', function (Blueprint $table) {
            // Revertir los cambios en caso de rollback
            $table->string('nationality')->after('phone');
            $table->integer('age')->after('nationality');

            // Eliminar las nuevas columnas
            $table->dropColumn(['country', 'birthdate']);
        });
    }
}
