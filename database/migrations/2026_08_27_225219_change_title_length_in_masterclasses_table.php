<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTitleLengthInMasterclassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masterclasses', function (Blueprint $table) {
            \DB::statement('ALTER TABLE masterclasses MODIFY title VARCHAR(255) NOT NULL;');
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
            \DB::statement('ALTER TABLE masterclasses MODIFY title VARCHAR(50) NOT NULL;');
        });
    }
}
