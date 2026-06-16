<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dinamica_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dinamica_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registro_id')->nullable()->constrained('dinamica_registros')->nullOnDelete();
            $table->unsignedInteger('turno_orden')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->string('premio_nombre')->nullable();
            $table->string('premio_tipo')->nullable();
            $table->unsignedInteger('angulo')->nullable();
            $table->timestamps();

            $table->unique(['dinamica_id', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dinamica_turnos');
    }
};
