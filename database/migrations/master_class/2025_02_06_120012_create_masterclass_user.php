<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterclassUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masterclass_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('masterclass_distributor_id')->unsigned();
            $table->string('name');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone');
            $table->integer('age');
            $table->string('nationality');
            $table->timestamps();

            $table->foreign('masterclass_distributor_id')
                ->references('id')->on('masterclass_distributor')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masterclass_user');
    }
}
