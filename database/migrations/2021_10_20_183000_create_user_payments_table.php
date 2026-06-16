<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('id_payment')->unsigned();
            $table->string('authorizationCode', 6)->nullable();
            $table->string('errorCode', 4);
            $table->integer('idCommerce');
            $table->string('shippingCity', 50)->nullable();
            $table->string('txDateTime', 20)->nullable();
            $table->integer('purchaseOperationNumber');
            $table->string('shippingAddress', 50);
            $table->string('card_account_type', 30)->nullable();
            $table->string('answerMessage');
            $table->string('bank_description',30)->nullable();
            $table->string('cuota',30);
            $table->string('paymentReferenceCode')->nullable();
            $table->string('brand', 12)->nullable();
            $table->text('purchaseVerification');
            $table->string('IDTransaction', 30);
            $table->string('errorMessage')->nullable();
            $table->string('authorizationResult', 3)->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_payment')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('user_payments');
    }
}
