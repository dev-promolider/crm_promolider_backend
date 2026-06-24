<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnNullableTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('class', 'time')) {
            Schema::table('class', function (Blueprint $table) {
                $table->time('time')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('class', 'time')) {
            Schema::table('class', function (Blueprint $table) {
                $table->time('time')->nullable(false)->change();
            });
        }
    }
}
