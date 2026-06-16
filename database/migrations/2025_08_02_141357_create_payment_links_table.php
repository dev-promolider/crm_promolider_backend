<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentLinksTable extends Migration
{
    public function up()
    {
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre descriptivo del link
            $table->string('slug')->unique(); // URL amigable
            $table->string('product_type'); // 'membership', 'opc', 'course', 'recharge'
            $table->unsignedBigInteger('product_id')->nullable(); // ID del producto específico
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->boolean('active')->default(true);
            $table->integer('usage_count')->default(0); // Contador de usos
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_links');
    }
}
