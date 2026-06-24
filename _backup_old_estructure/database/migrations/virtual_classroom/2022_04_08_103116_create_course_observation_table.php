<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseObservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_observation', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_analyst')->unsigned();
            $table->bigInteger('id_productor')->unsigned();
            $table->bigInteger('id_class')->unsigned();
            $table->bigInteger('id_course')->unsigned();
            $table->longText('description');
            $table->char('status', 1);
            $table->timestamps();
            $table->foreign('id_productor')->references('id')->on('users');
            $table->foreign('id_course')->references('id')->on('courses');
            $table->foreign('id_analyst')->references('id')->on('users');
            $table->foreign('id_class')->references('id')->on('class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_observation', function (Blueprint $table) {
            $table->dropForeign(['id_productor']);
            $table->dropForeign(['id_course']);
            $table->dropForeign(['id_analyst']);
            $table->dropForeign(['id_class']);
        });
        Schema::dropIfExists('course_observation');
    }
}
