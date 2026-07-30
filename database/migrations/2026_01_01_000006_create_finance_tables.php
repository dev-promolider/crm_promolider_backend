<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('account_type', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->decimal('price', 10, 2);
            $table->integer('iva')->default(0);
            $table->integer('fast_cash_bonus')->default(0);
            $table->integer('pay_in_binary')->default(0);
            $table->integer('productor_bonus')->default(0);
            $table->integer('course_selling_bonus')->default(0);
            $table->integer('disc_purchases_course')->default(0);
            $table->integer('disc_purchases_certificates')->default(0);
            $table->integer('enrollment_duration')->default(0);
            $table->integer('comission')->default(0);
            $table->string('status')->default('1');
            $table->timestamps();
        });

        Schema::create('account_type_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_type_id');
            $table->dateTime('purchase_date');
            $table->dateTime('expiration_date');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('account_type_id')->references('id')->on('account_type');
        });

        Schema::create('wallet', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->integer('status')->default(1);
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
        });

        Schema::create('bonus_type', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('wallet_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->double('amount');
            $table->tinyInteger('type'); // Ingreso/Egreso
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('id_receiver')->nullable();
            $table->string('reason')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_number')->nullable();
            $table->unsignedBigInteger('bonus_type_id')->nullable();
            $table->integer('batch')->nullable();
            $table->unsignedBigInteger('user_purchase_id')->nullable();
            $table->text('message')->nullable();
            $table->string('support_image')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallet');
            $table->foreign('id_receiver')->references('id')->on('users');
            $table->foreign('bonus_type_id')->references('id')->on('bonus_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_movements');
        Schema::dropIfExists('bonus_type');
        Schema::dropIfExists('wallet');
        Schema::dropIfExists('account_type_details');
        Schema::dropIfExists('account_type');
    }
};
