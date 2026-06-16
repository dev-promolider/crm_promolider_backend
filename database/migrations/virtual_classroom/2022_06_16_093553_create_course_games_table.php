<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_games', function (Blueprint $table) {
            $table->id();
            $table->foreign('game_type_id')->references('id')->on('games_types')->after('id');
            $table->unsignedBigInteger('game_type_id');

            $table->foreign('course_id')->references('id')->on('courses')->after('games_types');
            $table->unsignedBigInteger('course_id')->nullable();
            
            $table->foreign('module_id')->references('id')->on('modules')->after('course_id');
            $table->unsignedBigInteger('module_id')->nullable();
            
            $table->foreign('lesson_id')->references('id')->on('class')->after('modules');
            $table->unsignedBigInteger('lesson_id')->nullable();

            $table->string('title');
            $table->boolean('status');
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
        Schema::table('course_games', function (Blueprint $table) {
            $table->dropForeign(['game_type_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['module_id']);
            $table->dropForeign(['lesson_id']);
        });

        Schema::dropIfExists('course_games');
    }
}
