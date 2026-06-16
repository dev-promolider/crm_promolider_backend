<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiniCourseVideosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mini_course_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mini_course_id')->constrained()->onDelete('cascade');
            $table->string('video');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration')->nullable(); // duración en minutos
            $table->integer('order')->default(0); // para ordenar los videos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mini_course_videos');
    }
}
