<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnsCommentary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        Schema::table('commentary', function(Blueprint $table) {
            $table->unsignedInteger('issuing_user_id');
            $table->unsignedInteger('receiving_user_id');
            $table->longText('comments');
        });
    }
}
