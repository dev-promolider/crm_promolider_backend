<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHourAndDurationToMasterclasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masterclasses', function (Blueprint $table) {
            $table->time('hour')->nullable()->after('date');
            $table->integer('duration')->nullable()->after('hour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('masterclasses', function (Blueprint $table) {
            $table->dropColumn('hour');
            $table->dropColumn('duration');
        });
    }
}
