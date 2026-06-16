<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_modules')->unsigned();
            $table->string('name', 50);
            $table->time('time');
            $table->longText('url');
            $table->longText('description');
            $table->timestamps();
            $table->foreign('id_modules')->references('id')->on('modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class', function (Blueprint $table) {
            $table->dropForeign(['id_modules']);
        });
        Schema::dropIfExists('class');
    }
}
