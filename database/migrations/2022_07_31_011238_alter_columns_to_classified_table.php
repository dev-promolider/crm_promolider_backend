<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnsToClassifiedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classified', function (Blueprint $table) {
            $table->dropColumn('status_position_left');
            $table->dropColumn('status_position_right');
            $table->string('user_position_left')->nullable();
            $table->string('user_position_right')->nullable();
            $table->string('user_above')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classified', function (Blueprint $table) {
            //
        });
    }
}
