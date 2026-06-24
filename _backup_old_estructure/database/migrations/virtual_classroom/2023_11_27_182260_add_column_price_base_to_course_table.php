<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPriceBaseToCourseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('courses', 'price_base')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->double('price_base')->nullable();
            });
        }

        if (!Schema::hasColumn('courses', 'certificate')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->boolean('certificate')->default(0);
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
        if (Schema::hasColumn('courses', 'price_base')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('price_base');
            });
        }

        if (Schema::hasColumn('courses', 'certificate')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('certificate');
            });
        }
    }
}
