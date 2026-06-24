<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterclassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masterclasses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('id_categories')->unsigned();
            $table->string('title', 50);
            $table->longText('description');
            $table->longText('objectives');
            $table->date('date');
            $table->string('email_contact', 50);
            $table->string('phone_contact', 20);
            $table->boolean('status')->default(true);
            $table->string('meeting_link', 255);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('id_categories')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masterclasses');
    }
}
