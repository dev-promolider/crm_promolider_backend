<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('preregistro_links', function (Blueprint $table) {
            $table->id();

            $table->string('username')->unique();

            $table->enum('lado', [
                'izquierda',
                'derecha'
            ])->default('izquierda');

            $table->enum('landing', [
                'claro',
                'oscuro'
            ])->default('claro');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preregistro_links');
    }
};