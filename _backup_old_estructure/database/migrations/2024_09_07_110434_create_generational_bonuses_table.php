<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerationalBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generational_bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('range_name');
	    $table->decimal('g_1',5,2)->default(0.00);
	    $table->decimal('g_2',5,2)->default(0.00);
	    $table->decimal('g_3',5,2)->default(0.00);
	    $table->decimal('g_4',5,2)->default(0.00);
	    $table->decimal('g_5',5,2)->default(0.00);
	    $table->decimal('g_6',5,2)->default(0.00);
	    $table->decimal('g_7',5,2)->default(0.00);
	    $table->decimal('g_8',5,2)->default(0.00);
	    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generational_bonuses');
    }
}
