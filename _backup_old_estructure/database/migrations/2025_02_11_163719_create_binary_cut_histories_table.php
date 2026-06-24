<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBinaryCutHistoriesTable extends Migration
{
    public function up()
{
    if (!Schema::hasTable('binary_cut_histories')) {

        Schema::create('binary_cut_histories', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rank_id');

            $table->decimal('left_points', 10, 2);
            $table->decimal('right_points', 10, 2);
            $table->decimal('transferred_amount', 10, 2);

            $table->integer('batch');

            $table->timestamps();

            $table->index('user_id');
            $table->index('rank_id');
        });
    }
}

    public function down()
    {
        Schema::dropIfExists('binary_cut_histories');
    }
}
