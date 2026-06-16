<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_video', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_courses')->unsigned();
            $table->longText('url');
            $table->time('time');
            $table->timestamps();

            $table->foreign('id_courses')->references('id')->on('courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_video', function (Blueprint $table) {
            $table->dropForeign(['id_courses']);
        });
        Schema::dropIfExists('course_video');
    }
}
