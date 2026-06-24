<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRankingUserExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranking_user_exams', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exam');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->bigInteger('time');
            $table->smallInteger('points');
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
        Schema::table('ranking_user_exams', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('ranking_user_exams');
    }
}
