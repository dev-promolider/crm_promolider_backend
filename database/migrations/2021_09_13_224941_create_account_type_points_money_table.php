<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTypePointsMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_type_points_money', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_type_id')->unsigned();
            $table->foreign('account_type_id')->references('id')->on('account_type');
            $table->unsignedDouble('points',10,2)->default(0.0);
            $table->unsignedDouble('money',10,2)->default(0.0);
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
        Schema::dropIfExists('account_type_points_money');
    }
}
