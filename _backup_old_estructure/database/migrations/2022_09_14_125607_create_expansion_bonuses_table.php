<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpansionBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expansion_bonus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_account_type');
            $table->string('name');
            $table->integer('value');
            $table->timestamps();

            $table->foreign('id_account_type')->references('id')->on('account_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expansion_bonus');
    }
}
