<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versiones guardadas del plan de compensacion.
 *
 * Sustituyen al "contraste con el documento", que comparaba contra un archivo del
 * codigo que el administrador no podia cambiar. Ahora la configuracion del panel es
 * el plan, y cada version es una foto completa (membresias, rangos, porcentajes y
 * ajustes) para comparar con lo que hay y, si hace falta, volver atras.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compensation_plan_versions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('notes')->nullable();
            // longText y no json: MariaDB lo trata igual y evita sorpresas de version.
            $table->longText('snapshot');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compensation_plan_versions');
    }
};
