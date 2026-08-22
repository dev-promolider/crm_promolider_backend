<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPreviewToBookFilesTable extends Migration
{
    public function up()
    {
        Schema::table('book_files', function (Blueprint $table) {
            // Marca el archivo que se ofrece como muestra gratuita en el
            // marketplace. Solo puede haber uno por libro.
            $table->boolean('is_preview')->default(false)->after('size');
        });
    }

    public function down()
    {
        Schema::table('book_files', function (Blueprint $table) {
            $table->dropColumn('is_preview');
        });
    }
}
