<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('dinamica_trivia_configs')) {
            Schema::create('dinamica_trivia_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dinamica_id');
                $table->json('registration_config')->nullable();
                $table->json('trivia_config')->nullable();
                $table->json('game_blocks')->nullable();
                $table->timestamps();

                $table->foreign('dinamica_id')->references('id')->on('dinamicas')->onDelete('cascade');
                $table->unique('dinamica_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dinamica_trivia_configs');
    }
};
