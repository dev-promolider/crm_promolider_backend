<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReadingModeToCoursesTable extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Cómo entrega el productor el libro al comprador:
            //   online   -> solo lectura dentro de la plataforma
            //   download -> además puede descargar los archivos
            // Por defecto 'download', que es como se comportaba hasta ahora.
            $table->enum('reading_mode', ['online', 'download'])
                ->default('download')
                ->after('certificate');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('reading_mode');
        });
    }
}
