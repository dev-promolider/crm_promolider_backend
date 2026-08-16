<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_courses');
            $table->unsignedBigInteger('id_class')->nullable();
            $table->unsignedBigInteger('id_analyst')->nullable();
            $table->unsignedBigInteger('id_productor')->nullable();
            $table->text('observation')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_observations');
    }
}
