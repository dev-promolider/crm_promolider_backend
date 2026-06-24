<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseCertificatesTable extends Migration
{
    public function up()
    {
        Schema::create('course_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('certificate_templates');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('course_id')->constrained('courses');
            $table->date('completion_date');
            $table->json('custom_data')->nullable();
            $table->string('certificate_code')->unique();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'issued', 'revoked'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        
            // Índices
            $table->index(['user_id', 'course_id']);
            $table->unique(['user_id', 'course_id']); // Un usuario solo puede tener un certificado por curso
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_certificates');
    }
}
