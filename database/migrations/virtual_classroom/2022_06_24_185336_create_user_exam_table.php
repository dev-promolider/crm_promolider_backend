<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserExamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_exam_header', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('rate');
            $table->boolean('status');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('productor_id');
            $table->foreign('productor_id')->references('id')->on('users');
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exam');
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
        Schema::table('user_exam_header', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['productor_id']);
            $table->dropForeign(['exam_id']);
        });

        Schema::dropIfExists('user_exam_header');
    }
}
