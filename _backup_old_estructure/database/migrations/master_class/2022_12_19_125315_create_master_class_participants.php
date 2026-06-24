<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterClassParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_class_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_class_id');
            $table->foreign('master_class_id')->references('id')->on('master_class_video');
            $table->text('fullname');
            $table->text('email');
            $table->text('phone');
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
        Schema::dropIfExists('master_class_participants');
    }
}
