<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnIdCourseVideoClassResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_resources', function ($table) {
            $table->dropForeign(['id_course_video']);
            $table->dropColumn('id_course_video');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_resources', function (Blueprint $table) {
            $table->unsignedBigInteger('id_course_video')->nullable();
            $table->foreign('id_course_video')->references('id')->on('course_video');
        });
    }
}
