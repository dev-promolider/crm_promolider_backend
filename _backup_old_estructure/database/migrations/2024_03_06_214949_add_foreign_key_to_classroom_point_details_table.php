<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToClassroomPointDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classroom_point_details', function (Blueprint $table) {
            if (!Schema::hasColumn('classroom_point_details', 'id_course_games')) {
                $table->foreignId('id_course_games')->constrained('course_games');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classroom_point_details', function (Blueprint $table) {
            $table->dropForeign(['id_course_games']);
            $table->dropColumn('id_course_games');
        });
    }
}
