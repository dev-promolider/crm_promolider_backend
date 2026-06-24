<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnConditionToCertificateToCourseConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_configuration', function (Blueprint $table) {
            //0:ver videos | 1:aprobar examenes | 2:ver videos y aprobar examenes
            $table->tinyInteger('condition_to_certificate')->after('data')
                ->comment('0:ver videos | 1:aprobar examenes | 2:ver videos y aprobar examenes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificate_to_course_configuration', function (Blueprint $table) {
            //
        });
    }
}
