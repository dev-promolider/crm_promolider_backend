<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['masterclasses', 'mini_courses', 'ebooks', 'dinamicas'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'course_id')) {
                        $table->unsignedBigInteger('course_id')->nullable()->after('id');
                    }
                });

                // Asignar curso genérico del creador a los registros existentes
                $records = DB::table($tableName)->whereNull('course_id')->get();
                foreach ($records as $record) {
                    $course = DB::table('courses')->where('user_id', $record->user_id)->first();
                    if ($course) {
                        DB::table($tableName)
                            ->where('id', $record->id)
                            ->update(['course_id' => $course->id]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = ['masterclasses', 'mini_courses', 'ebooks', 'dinamicas'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'course_id')) {
                        $table->dropColumn('course_id');
                    }
                });
            }
        }
    }
};
