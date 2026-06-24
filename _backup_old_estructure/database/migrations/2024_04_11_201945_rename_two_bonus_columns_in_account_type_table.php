<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameTwoBonusColumnsInAccountTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::statement('ALTER TABLE account_type CHANGE profit_on_purchases productor_bonus DOUBLE UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE account_type CHANGE profit_on_purchases_2 course_selling_bonus DOUBLE UNSIGNED NOT NULL DEFAULT 0');
        
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::statement('ALTER TABLE account_type CHANGE productor_bonus profit_on_purchases DOUBLE UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE account_type CHANGE course_selling_bonus profit_on_purchases_2 DOUBLE UNSIGNED NOT NULL DEFAULT 0');
    }
}
