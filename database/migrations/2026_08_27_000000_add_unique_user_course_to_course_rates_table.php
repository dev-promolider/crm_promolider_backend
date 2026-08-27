<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueUserCourseToCourseRatesTable extends Migration
{
    /**
     * Un usuario solo puede registrar una valoración por curso.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_rates', function (Blueprint $table) {
            $table->unique(['user_id', 'course_id'], 'course_rates_user_course_unique');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('course_rates', function (Blueprint $table) {
            $table->dropUnique('course_rates_user_course_unique');
        });
    }
}
