<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBinanceAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('binance_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('account_name'); // Nombre del titular
            $table->string('binance_id'); // ID de Binance
            $table->string('phone')->nullable(); // Teléfono asociado
            $table->string('network')->default('BSC'); // Red (BSC, ETH, etc.)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('binance_accounts');
    }
}
