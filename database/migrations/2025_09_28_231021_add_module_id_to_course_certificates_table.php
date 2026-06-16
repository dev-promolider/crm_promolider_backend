<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModuleIdToCourseCertificatesTable extends Migration
{
    public function up()
    {
        Schema::table('course_certificates', function (Blueprint $table) {
            $table->unsignedBigInteger('module_id')->nullable()->after('course_id');
        
            // Si quieres que se relacione con la tabla modules:
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::table('course_certificates', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });
    }
}
