<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('operation_number');
            $table->string('authorization');
            $table->string('operation_type');
            $table->string('transaction_type');
            $table->string('status');
            $table->string('conciliated');
            $table->timestampTz('creation_date');
            // $table->timestampTz('operation_date');
            $table->timestampTz('operation_date')->default(now());
            $table->string('description');
            $table->string('error_message')->nullable();
            $table->string('order_id');
            $table->json('card');
            // $table->timestampTz('due_date');
            $table->timestampTz('due_date')->nullable();
            $table->decimal('amount',10,4);
            $table->json('customer');
            $table->json('fee');
            $table->json('payment_method');
            $table->json('metadata');
            $table->string('currency');
            $table->string('method');
            
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
        Schema::dropIfExists('transactions');
    }
}
