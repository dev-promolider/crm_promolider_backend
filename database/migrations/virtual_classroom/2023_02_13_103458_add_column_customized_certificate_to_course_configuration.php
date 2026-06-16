<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCustomizedCertificateToCourseConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_configuration', function (Blueprint $table) {
            $table->tinyInteger('customized_certificate')->default(0)->after('validated_by')->comment('0: no | 1: si');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_configuration', function (Blueprint $table) {
            //
        });
    }
}
