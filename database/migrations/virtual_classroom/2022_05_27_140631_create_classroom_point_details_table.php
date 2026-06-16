<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassroomPointDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    // Cambiar nombre de la tabla por uno que haga referencia a una configuracion general
    // La idea es que se puedan configurar los puntos de gamificacion del aula virtual
    public function up()
    {
        Schema::create('classroom_point_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user_classroom_points');
            $table->integer('increment_points');
            $table->string('description');       
            $table->timestamps();

            $table->foreign('id_user_classroom_points')->references('id')->on('user_classroom_points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classroom_point_details', function (Blueprint $table) {
            $table->dropForeign(['id_user_classroom_points']);
        });
        Schema::dropIfExists('classroom_point_details');
    }
}
