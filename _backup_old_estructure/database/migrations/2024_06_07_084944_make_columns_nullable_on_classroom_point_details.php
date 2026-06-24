<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsNullableOnClassroomPointDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classroom_point_details', function (Blueprint $table) {
            $table->integer('completion_time')->nullable()->change();

            if ($this->foreignKeyExists('classroom_point_details', 'classroom_point_details_id_course_games_foreign')) {
                Schema::table('classroom_point_details', function (Blueprint $table) {
                    $table->dropForeign(['id_course_games']);
                });
            }

            $table->unsignedBigInteger('id_course_games')->nullable()->change();

            $table->foreign('id_course_games')->references('id')->on('course_games')->cascadeOnDelete();
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
            
            $table->integer('completion_time')->nullable(false)->change();

            
            if ($this->foreignKeyExists('classroom_point_details', 'classroom_point_details_id_course_games_foreign')) {
                Schema::table('classroom_point_details', function (Blueprint $table) {
                    $table->dropForeign(['id_course_games']);
                });
            }

            
            $table->unsignedBigInteger('id_course_games')->nullable(false)->change();

            
            $table->foreign('id_course_games')->references('id')->on('course_games')->cascadeOnDelete();
        });
    }


    protected function foreignKeyExists($table, $foreignKey)
    {
        $connection = Schema::getConnection()->getDoctrineSchemaManager();
        $foreignKeys = $connection->listTableForeignKeys($table);

        foreach ($foreignKeys as $key) {
            if ($key->getName() === $foreignKey) {
                return true;
            }
        }

        return false;
    }

}
