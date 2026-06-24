<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_type', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->unsignedDouble('price',10,2)->default(0.0);
            $table->unsignedDouble('iva',10,2)->default(0.0);
            $table->unsignedDouble('fast_cash_bonus',10,2)->default(0);
            $table->unsignedDouble('disc_purchases',10,2)->default(0);
            $table->unsignedDouble('pay_in_binary',10,2)->default(0);
            $table->unsignedDouble('profit_on_purchases',10,2)->default(0);
            $table->unsignedDouble('profit_on_purchases_2',10,2)->default(0);
            $table->unsignedDouble('comission',10,2)->default(0);
            $table->string('status', 1)->default('1');
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
        Schema::dropIfExists('account_type');
    }
}
