<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbookUsersTable extends Migration
{
    public function up()
    {
        Schema::create('ebook_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ebook_distributor_id');
            $table->string('name');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone');
            $table->integer('age');
            $table->string('nationality');
            $table->timestamps();

            $table->foreign('ebook_distributor_id')
                  ->references('id')->on('ebook_distributor')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ebook_users');
    }
}
