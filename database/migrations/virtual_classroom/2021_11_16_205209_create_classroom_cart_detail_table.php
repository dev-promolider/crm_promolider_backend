<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassroomCartDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classroom_cart_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('classroom_cart_id')->unsigned();
            $table->bigInteger('courses_id')->unsigned();
            $table->timestamps();
            $table->foreign('classroom_cart_id')->references('id')->on('classroom_cart');
            $table->foreign('courses_id')->references('id')->on('courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classroom_cart_detail');
    }
}
