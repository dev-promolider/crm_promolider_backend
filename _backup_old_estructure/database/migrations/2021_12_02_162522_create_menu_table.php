<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('badge')->nullable();
            $table->string('badgeClass')->nullable();
            $table->string('icon')->nullable();
            $table->string('slug')->nullable();
            $table->string('dropdown')->nullable();
            $table->string('url')->nullable();
            $table->string('navheader')->nullable();
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
        Schema::dropIfExists('menu');
    }
}
