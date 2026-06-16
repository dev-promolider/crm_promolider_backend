<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTableCourseExam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->change();
            $table->unsignedBigInteger('module_id')->nullable()->after('course_id');
            $table->foreign('module_id')->references('id')->on('modules');
            $table->unsignedBigInteger('lesson_id')->nullable()->after('module_id');
            $table->foreign('lesson_id')->references('id')->on('class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exam', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropForeign(['lesson_id']);
            // $table->dropColumn('module_id');
            // $table->dropColumn('lesson_id');
        });
        // Schema::dropIfExists('exam');
    }
}
