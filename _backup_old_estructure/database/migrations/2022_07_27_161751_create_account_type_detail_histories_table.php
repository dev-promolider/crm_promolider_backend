<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTypeDetailHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_type_detail_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_type_id')->unsigned();
            $table->bigInteger('account_type_detail_id')->unsigned();
            $table->dateTime('purchase_date');
            $table->dateTime('expiration_date');
            $table->boolean('status');
            $table->foreign('account_type_id')->references('id')->on('account_type');
            $table->foreign('account_type_detail_id')->references('id')->on('account_type_details');
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
        Schema::table('account_type_detail_histories', function (Blueprint $table) {
            $table->dropForeign(['account_type_id']);
            $table->dropForeign(['account_type_detail_id']);
        });
        Schema::dropIfExists('account_type_detail_histories');
    }
}
