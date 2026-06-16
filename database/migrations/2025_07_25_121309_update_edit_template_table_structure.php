<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateEditTemplateTableStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('edit_template', function (Blueprint $table) {
            // Eliminar foreign key existente primero
            $table->dropForeign(['template_id']);
            
            // Eliminar campos antiguos (excepto template_id que lo necesitamos)
            $table->dropColumn(['section_name', 'content']);
            
            // Agregar nuevos campos
            $table->foreignId('user_id')->after('id'); // creador
            $table->string('title')->after('template_id'); // título personalizado
            $table->longText('content_html')->after('title'); // HTML completo generado
            $table->json('edited_fields')->nullable()->after('content_html'); // JSON con los campos editados
            
            // Recrear foreign keys con las nuevas referencias
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('template')->onDelete('cascade');
        });
        
        // Modificar campo status usando SQL directo
        DB::statement("ALTER TABLE edit_template MODIFY COLUMN status ENUM('draft', 'published') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('edit_template', function (Blueprint $table) {
            // Eliminar foreign keys nuevas
            $table->dropForeign(['user_id']);
            $table->dropForeign(['template_id']);
            
            // Eliminar columnas nuevas
            $table->dropColumn(['user_id', 'title', 'content_html', 'edited_fields']);
            
            // Restaurar columnas anteriores
            $table->string('section_name')->after('template_id');
            $table->json('content')->nullable()->after('section_name');
            
            // Restaurar foreign key anterior
            $table->foreign('template_id')->references('id')->on('template')->onDelete('cascade');
        });
    }
}
