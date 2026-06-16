<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable(); // imagen 
            $table->longText('content_html'); // HTML original de la plantilla
            $table->integer('membresia'); // NUMERO - cambié 'int' por 'integer'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template', function (Blueprint $table) {
            $table->dropColumn(['description', 'thumbnail', 'content_html', 'membresia']);
        });
    }
}
