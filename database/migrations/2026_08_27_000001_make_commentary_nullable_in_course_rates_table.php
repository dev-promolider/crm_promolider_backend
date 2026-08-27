<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCommentaryNullableInCourseRatesTable extends Migration
{
    /**
     * Permite registrar valoraciones sin comentario escrito.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('course_rates', function (Blueprint $table) {
            $table->string('commentary', 255)->nullable()->change();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('course_rates', function (Blueprint $table) {
            $table->string('commentary', 255)->nullable(false)->change();
        });
    }
}
