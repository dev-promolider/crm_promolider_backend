<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameMiniCourseVideosToMiniCourseClasses extends Migration
{
    public function up(): void
    {
        Schema::rename('mini_course_videos', 'mini_course_classes');
    }

    public function down(): void
    {
        Schema::rename('mini_course_classes', 'mini_course_videos');
    }
}
