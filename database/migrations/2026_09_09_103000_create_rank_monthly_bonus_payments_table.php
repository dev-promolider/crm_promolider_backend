<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bono mensual de rango ya entregado, mes a mes y persona a persona.
 *
 * El plan lo promete desde el rango de Director ("manteniendote en este rango por 3
 * meses consecutivos se activa una bonificacion mensual"), la columna monthly_bonus
 * de rank_bonus lleva anos configurada y nunca se ha pagado en ningun sitio.
 *
 * Va en su propia tabla y con su propio periodo, separado del corte binario, porque
 * el rango es mensual aunque el corte llegue a ser quincenal: aunque haya dos cortes
 * en un mes, este bono se paga una sola vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_monthly_bonus_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rank_bonus_id');
            $table->string('period_key', 16);          // 2026-09
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('streak')->default(0); // meses seguidos en el rango
            $table->timestamps();

            $table->unique(['user_id', 'period_key'], 'rank_monthly_bonus_user_period_unique');
            $table->index('rank_bonus_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_monthly_bonus_payments');
    }
};
