<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('puchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->string('trasaction_id', 600);
            $table->integer('log_id');
            $table->string('product_id');
            $table->string('product_name');
            $table->string('product_quantity');
            $table->string('product_amount');
            $table->string('payer_fname');
            $table->string('payer_lname');
            $table->string('payer_address');
            $table->string('payer_city');
            $table->string('payer_state');
            $table->string('payer_zip');
            $table->string('payer_country');
            $table->text('payer_email');
            $table->string('payment_status');
            $table->datetime('posted_date');
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
        Schema::dropIfExists('puchases');
    }
}
