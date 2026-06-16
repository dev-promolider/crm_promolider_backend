<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('trivia_user_answers')) {
            return;
        }

        Schema::create('trivia_user_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dinamica_id');
            $table->unsignedBigInteger('dinamica_registro_id');
            $table->unsignedBigInteger('question_item_id');
            $table->unsignedInteger('numero_pregunta');
            $table->unsignedInteger('opcion_indice')->nullable();
            $table->string('opcion_texto')->nullable();
            $table->boolean('es_correcta')->default(false);
            $table->decimal('valor_pregunta', 8, 2)->default(0);
            $table->decimal('puntos_obtenidos', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['dinamica_registro_id', 'question_item_id'], 'uqa_registro_pregunta_idx');
            $table->foreign('dinamica_id')->references('id')->on('dinamicas')->onDelete('cascade');
            $table->foreign('dinamica_registro_id')->references('id')->on('dinamica_registros')->onDelete('cascade');
            $table->foreign('question_item_id')->references('id')->on('question_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trivia_user_answers');
    }
};
