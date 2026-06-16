<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('id_categories')->unsigned();
            $table->string('title', 50);
            $table->string('area', 20);
            $table->longText('description');
            $table->string('image', 255);
            $table->string('currency', 20);
            $table->double('price');
            $table->double('ranking_by_user',2,1)->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_categories')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['id_categories']);
        });
        Schema::dropIfExists('courses');
    }
}
