<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnVideoImagenToCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('courses', 'path_url')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->text('path_url')->nullable();
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
        if (Schema::hasColumn('courses', 'path_url')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('path_url');
            });
        }
    }
}
