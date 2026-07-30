<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_method', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('1');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_id'); // Aseguramos la relación que pediste
            $table->double('amount');
            $table->string('operation_number');
            $table->string('ex_bonus')->default('0');
            $table->text('details')->nullable();
            $table->string('receipt_image')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
        });

        Schema::create('binance_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email')->nullable();
            $table->string('account_name')->nullable();
            $table->string('binance_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('network')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('paypal_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email');
            $table->string('account_name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('currency')->default('USD');
            $table->enum('account_type', ['PERSONAL', 'BUSINESS'])->default('PERSONAL');
            $table->tinyInteger('is_verified')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('openpay_pending_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('product_id')->nullable();
            $table->string('product_name');
            $table->string('openpay_order_id');
            $table->string('product_detail')->nullable();
            $table->string('product_price')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('openpay_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('operation_number')->nullable();
            $table->string('authorization')->nullable();
            $table->string('operation_type')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('status')->nullable();
            $table->string('conciliated')->nullable();
            $table->timestamp('creation_date')->nullable();
            $table->timestamp('operation_date')->nullable();
            $table->string('description')->nullable();
            $table->string('error_message')->nullable();
            $table->string('order_id')->nullable();
            $table->longText('card')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->decimal('amount', 12, 4)->nullable();
            $table->longText('customer')->nullable();
            $table->longText('fee')->nullable();
            $table->longText('payment_method_data')->nullable();
            $table->longText('metadata')->nullable();
            $table->string('currency')->nullable();
            $table->string('method')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('openpay_webhooks');
        Schema::dropIfExists('openpay_pending_payments');
        Schema::dropIfExists('paypal_accounts');
        Schema::dropIfExists('binance_accounts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_method');
    }
};
