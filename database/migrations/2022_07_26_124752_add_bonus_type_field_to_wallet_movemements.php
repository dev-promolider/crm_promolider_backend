<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBonusTypeFieldToWalletMovemements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('bonus_type_id')->nullable();
            $table->foreign('bonus_type_id')->references('id')->on('bonus_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_movements', function (Blueprint $table) {
            $table->dropForeign(['bonus_type_id']);
        });
    }
}
