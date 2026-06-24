<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAddModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('modules', 'order')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->bigInteger('order')->nullable();
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
        if (Schema::hasColumn('modules', 'order')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
}
