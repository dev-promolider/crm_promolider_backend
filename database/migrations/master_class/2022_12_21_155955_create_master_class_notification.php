<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterClassNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_class_notification', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transmitter');
            $table->foreign('transmitter')->references('id')->on('users');
            $table->unsignedBigInteger('receiver');
            $table->foreign('receiver')->references('id')->on('users');
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('seen')->default(false);
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
        Schema::dropIfExists('master_class_notification');
    }
}
