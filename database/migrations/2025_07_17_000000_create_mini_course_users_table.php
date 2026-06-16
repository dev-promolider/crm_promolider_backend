<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiniCourseUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mini_course_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mini_course_distributor_id');
            $table->string('name');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone');
            $table->integer('age');
            $table->string('nationality');
            $table->timestamps();

            $table->foreign('mini_course_distributor_id')
                  ->references('id')->on('mini_course_distributors')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mini_course_users');
    }
}
