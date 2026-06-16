<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassifiedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classified', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('id_user_sponsor')->unsigned();
            $table->string('binary_sponsor', 50);
            $table->string('position', 2);
            $table->integer('classification')->default('1');
            $table->string('status', 1)->default('0');
            $table->string('authorized', 1)->default('1');
            $table->string('status_position_left', 1)->default('0');
            $table->string('status_position_right', 1)->default('0');
            $table->unsignedDouble('growth_bonus',10,2)->default(0.0);
            $table->unsignedDouble('starting_bonus',10,2)->default(0.0);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_user_sponsor')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classified');
    }
}
