<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->double('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->string('status', 1)->default('0');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('wallet', function (Blueprint $table) {
        //     $table->dropForeign('payment_id');
        //     $table->dropColumn('payment_id');
        // });

        Schema::dropIfExists('wallet');
    }
}
