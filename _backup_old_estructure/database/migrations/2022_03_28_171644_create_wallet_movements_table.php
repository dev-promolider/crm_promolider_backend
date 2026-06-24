<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_movements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('wallet_id')->unsigned();
            $table->double('amount', 10, 2);
            $table->tinyInteger('type'); //tipo de bono
            $table->tinyInteger('status')->default(1); //0=> corte aplicado - 1 => a espera de corte binario
            $table->unsignedBigInteger('id_receiver')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->foreign('wallet_id')->references('id')->on('wallet');
            $table->foreign('id_receiver')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_movements');
    }
}
