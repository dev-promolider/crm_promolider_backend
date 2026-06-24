<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLessonProgressTable extends Migration
{
    public function up(): void
{
    if (!Schema::hasTable('user_lesson_progress')) {

        Schema::create('user_lesson_progress', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('lesson_id');

            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Índices
            $table->unique(['user_id', 'course_id', 'lesson_id']);
            $table->index(['user_id', 'course_id']);
        });

        // Agregar foreign keys manualmente (más seguro en prod desincronizada)
        try {
            Schema::table('user_lesson_progress', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->foreign('lesson_id')->references('id')->on('classes')->onDelete('cascade');
            });
        } catch (\Exception $e) {}
    }
}

    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
}
