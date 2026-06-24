<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDinamicaRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dinamica_registros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dinamica_id');
            $table->string('nombre')->nullable();
            $table->string('email');
            $table->timestamps();

            $table->foreign('dinamica_id')->references('id')->on('dinamicas')->onDelete('cascade');
            $table->unique(['dinamica_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dinamica_registros');
    }
}
