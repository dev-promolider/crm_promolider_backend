<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificateSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Agregar progress a purchased_courses
        if (Schema::hasTable('purchased_courses')) {
            Schema::table('purchased_courses', function (Blueprint $table) {
                if (!Schema::hasColumn('purchased_courses', 'progress')) {
                    $table->decimal('progress', 5, 2)->default(0)->after('course_id');
                }
            });
        }

        // 2. Crear tabla user_course_progress
        if (!Schema::hasTable('user_course_progress')) {
            Schema::create('user_course_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->integer('progress')->default(0);
                $table->timestamps();
                
                $table->unique(['user_id', 'course_id']);
                $table->index(['user_id', 'course_id']);
            });
        }

        // 4. Crear tabla certificate_templates
        if (!Schema::hasTable('certificate_templates')) {
            Schema::create('certificate_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('design_data');
                $table->text('html_template');
                $table->string('preview_image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. Crear tabla course_certificates
        if (!Schema::hasTable('course_certificates')) {
            Schema::create('course_certificates', function (Blueprint $table) {
                $table->id();
                $table->string('instructor_signature_path', 255)->nullable();
                $table->foreignId('template_id')->constrained('certificate_templates');
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('course_id')->constrained('courses');
                $table->unsignedBigInteger('module_id')->nullable();
                $table->date('completion_date');
                $table->json('custom_data')->nullable();
                $table->string('certificate_code')->unique();
                $table->string('pdf_path')->nullable();
                $table->enum('status', ['draft', 'issued', 'revoked'])->default('draft');
                $table->timestamp('issued_at')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'course_id']);
                $table->unique(['user_id', 'course_id']);
                
                // Foreign key para module_id
                $table->foreign('module_id')
                    ->references('id')
                    ->on('modules')
                    ->onDelete('cascade');
            });
        }

        // 6. Agregar columnas a courses
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (!Schema::hasColumn('courses', 'instructor_signature_path')) {
                    $table->string('instructor_signature_path', 255)->nullable()->after('id');
                }
                
                if (!Schema::hasColumn('courses', 'certificate_template_id')) {
                    $table->unsignedBigInteger('certificate_template_id')->nullable()->after('certificate');
                    
                    $table->foreign('certificate_template_id')
                        ->references('id')
                        ->on('certificate_templates')
                        ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar en orden inverso para respetar las foreign keys
        
        // 1. Eliminar columnas de courses
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'certificate_template_id')) {
                    $table->dropForeign(['certificate_template_id']);
                    $table->dropColumn('certificate_template_id');
                }
                
                if (Schema::hasColumn('courses', 'instructor_signature_path')) {
                    $table->dropColumn('instructor_signature_path');
                }
            });
        }

        // 2. Eliminar tabla course_certificates
        Schema::dropIfExists('course_certificates');

        // 3. Eliminar tabla certificate_templates
        Schema::dropIfExists('certificate_templates');

        // 5. Eliminar tabla user_course_progress
        Schema::dropIfExists('user_course_progress');

        // 6. Eliminar columna progress de purchased_courses
        if (Schema::hasTable('purchased_courses')) {
            Schema::table('purchased_courses', function (Blueprint $table) {
                if (Schema::hasColumn('purchased_courses', 'progress')) {
                    $table->dropColumn('progress');
                }
            });
        }
    }
}
