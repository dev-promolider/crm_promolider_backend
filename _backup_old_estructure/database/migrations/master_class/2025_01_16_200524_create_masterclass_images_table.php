<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterclassImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masterclass_images', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('masterclass_id')->unsigned();
            $table->string('image', 255);
            $table->timestamps();

            $table->foreign('masterclass_id')->references('id')->on('masterclasses')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masterclass_images');
    }
}
