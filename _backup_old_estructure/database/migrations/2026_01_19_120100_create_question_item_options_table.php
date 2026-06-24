<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_item_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('question_item_id')->constrained('question_items')->cascadeOnDelete();
            $table->string('label', 5)->nullable();
            $table->string('text', 255);
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['question_item_id', 'position']);
            $table->index('is_correct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_item_options');
    }
};
