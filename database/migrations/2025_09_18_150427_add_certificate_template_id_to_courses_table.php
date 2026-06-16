<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCertificateTemplateIdToCoursesTable extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('certificate_template_id')->nullable()->after('certificate');

            // 🔑 Relación con certificate_templates
            $table->foreign('certificate_template_id')
                  ->references('id')
                  ->on('certificate_templates')
                  ->onDelete('set null'); // si se borra el template, el curso mantiene null
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Primero elimina la foreign key
            $table->dropForeign(['certificate_template_id']);

            // Luego elimina la columna
            $table->dropColumn('certificate_template_id');
        });
    }
}
