<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaypalAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('paypal_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email'); // Email de PayPal
            $table->string('account_name'); // Nombre del titular
            $table->string('country_code', 2)->default('US'); // Código de país
            $table->string('currency', 3)->default('USD'); // Moneda
            $table->enum('account_type', ['personal', 'business'])->default('personal');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paypal_accounts');
    }
}
