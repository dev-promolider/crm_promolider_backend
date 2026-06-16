<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRankBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rank_bonus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('vol_min');
            $table->integer('pack_max');
            $table->integer('active_direct');
            $table->integer('max_pay');
            $table->integer('monthly_bonus')->default(0);
            $table->integer('extra_bonus')->default(0);
            $table->integer('limit_generation')->default(1);
            $table->string('icon');
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
        Schema::dropIfExists('rank_bonus');
    }
}
