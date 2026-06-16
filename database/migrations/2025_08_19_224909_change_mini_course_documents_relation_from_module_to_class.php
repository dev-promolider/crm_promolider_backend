<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMiniCourseDocumentsRelationFromModuleToClass extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Crear nueva columna si no existe
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('mini_course_documents', 'mini_course_class_id')) {

            Schema::table('mini_course_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('mini_course_class_id')
                      ->nullable()
                      ->after('mini_course_id');
            });

            // Crear índice manualmente (seguro)
            DB::statement('CREATE INDEX mini_course_documents_mini_course_class_id_index 
                           ON mini_course_documents (mini_course_class_id)');
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Migrar datos si ambas columnas existen
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasColumn('mini_course_documents', 'mini_course_module_id') &&
            Schema::hasColumn('mini_course_documents', 'mini_course_class_id')
        ) {

            DB::statement("
                UPDATE mini_course_documents 
                SET mini_course_class_id = (
                    SELECT MIN(id) 
                    FROM mini_course_classes 
                    WHERE mini_course_classes.mini_course_module_id = mini_course_documents.mini_course_module_id
                )
                WHERE mini_course_module_id IS NOT NULL
            ");

            DB::statement("
                DELETE FROM mini_course_documents 
                WHERE mini_course_module_id IS NOT NULL 
                AND mini_course_class_id IS NULL
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Eliminar columna antigua si existe
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('mini_course_documents', 'mini_course_module_id')) {

            try {
                Schema::table('mini_course_documents', function (Blueprint $table) {
                    $table->dropForeign(['mini_course_module_id']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('mini_course_documents', function (Blueprint $table) {
                    $table->dropColumn('mini_course_module_id');
                });
            } catch (\Exception $e) {}
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Hacer la nueva columna NOT NULL si existe
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('mini_course_documents', 'mini_course_class_id')) {

            try {
                Schema::table('mini_course_documents', function (Blueprint $table) {
                    $table->unsignedBigInteger('mini_course_class_id')
                          ->nullable(false)
                          ->change();
                });
            } catch (\Exception $e) {}
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mini_course_documents', function (Blueprint $table) {
            // Agregar de vuelta la columna antigua
            $table->unsignedBigInteger('mini_course_module_id')->nullable()->after('mini_course_id');
            
            // Agregar el índice y llave foránea para la columna antigua
            $table->index('mini_course_module_id');
            $table->foreign('mini_course_module_id')
                  ->references('id')
                  ->on('mini_course_modules')
                  ->onDelete('cascade');
        });

        // Migrar datos de vuelta de clase a módulo
        DB::statement("
            UPDATE mini_course_documents 
            SET mini_course_module_id = (
                SELECT mini_course_module_id 
                FROM mini_course_classes 
                WHERE mini_course_classes.id = mini_course_documents.mini_course_class_id
            )
            WHERE mini_course_class_id IS NOT NULL
        ");

        Schema::table('mini_course_documents', function (Blueprint $table) {
            // Eliminar la nueva columna
            $table->dropForeign(['mini_course_class_id']);
            $table->dropIndex(['mini_course_class_id']);
            $table->dropColumn('mini_course_class_id');
            
            // Hacer que la columna antigua sea requerida
            $table->unsignedBigInteger('mini_course_module_id')->nullable(false)->change();
        });
    }
}
