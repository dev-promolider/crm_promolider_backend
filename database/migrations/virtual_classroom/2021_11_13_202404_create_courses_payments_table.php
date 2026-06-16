<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses_payments', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained();
            $table->foreignId('payment_id')->constrained();
            $table->unsignedDouble('desc',10,2)->nullable();
            $table->unsignedDouble('price',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses_payments');
    }
}
