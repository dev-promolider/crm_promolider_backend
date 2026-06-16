<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserQuestionAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_question_answer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_exam_id');
            $table->foreign('user_exam_id')->references('id')->on('user_exam_header');
            $table->json('options_selected');
            $table->tinyInteger('points_gained');
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
        Schema::table('user_question_answer', function (Blueprint $table) {
            $table->dropForeign(['user_exam_id']);
        });

        Schema::dropIfExists('user_question_answer');
    }
}
