<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCouserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_couser', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('couser_id')->unsigned();
            $table->bigInteger('payment_id')->unsigned();
            $table->timestamps();

            $table->foreign('couser_id')->references('id')->on('courses');
            $table->foreign('payment_id')->references('id')->on('payments');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_couser');
    }
}
