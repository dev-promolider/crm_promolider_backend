<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMiniCourseModuleIdOnMiniCourseVideos extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mini_course_videos', function (Blueprint $table) {
            $table->unsignedBigInteger('mini_course_module_id')->nullable()->after('mini_course_id');
            $table->foreign('mini_course_module_id')->references('id')->on('mini_course_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mini_course_videos', function (Blueprint $table) {
            $table->dropForeign(['mini_course_module_id']);
            $table->dropColumn('mini_course_module_id');
        });
    }
}
