<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDescriptionVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('description_video', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_course_video')->unsigned();
            $table->longText('text');
            $table->timestamps();
            
            $table->foreign('id_course_video')->references('id')->on('course_video');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('description_video');
    }
}
