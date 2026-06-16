<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStatusToVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id');
            $table->enum('status', ['SEEN', 'NOT SEEN'])->default('NOT SEEN');
            
            $table->foreign("class_id")->references('id')->on('class');
        });
    }
}
