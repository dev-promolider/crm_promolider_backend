<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDinamicaPremiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dinamica_premios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dinamica_id');
            $table->string('nombre');
            $table->string('tipo');
            $table->integer('stock')->default(1);
            $table->integer('peso')->default(1);
            $table->integer('limite_usuario')->default(0); // 0 = sin límite
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fin')->nullable();
            $table->text('claim_url')->nullable();
            $table->timestamps();

            $table->foreign('dinamica_id')->references('id')->on('dinamicas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dinamica_premios');
    }
}
