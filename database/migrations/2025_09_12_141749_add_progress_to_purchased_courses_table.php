<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressToPurchasedCoursesTable extends Migration
{
    public function up(): void
    {
        Schema::table('purchased_courses', function (Blueprint $table) {
            $table->decimal('progress', 5, 2)->default(0)->after('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchased_courses', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
    }
}
