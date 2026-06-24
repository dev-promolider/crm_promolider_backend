<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_generator')->nullable()->references('id')->on('users')->constrained();
            $table->foreignId('id_receiver')->nullable()->references('id')->on('users')->constrained();
            $table->foreignId('id_badge')->nullable()->references('id')->on('badges')->constrained();
            $table->string('title');
            $table->longText('body');
            $table->tinyInteger('type');
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
        Schema::dropIfExists('notifications');
    }
}
