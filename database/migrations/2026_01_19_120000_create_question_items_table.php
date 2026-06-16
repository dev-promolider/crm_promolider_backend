<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('question_category_id')->constrained('question_categories')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('difficulty', 20)->default('medium');
            $table->unsignedInteger('time_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['question_category_id', 'status']);
            $table->index(['difficulty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_items');
    }
};
