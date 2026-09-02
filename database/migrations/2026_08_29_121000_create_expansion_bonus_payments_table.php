<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de tramos de bono de expansion ya pagados.
 *
 * El comando del monolito contaba cada mes los directos creados antes de la ultima
 * entrega y volvia a pagar sobre los mismos, asi que el bono se cobraba una y otra vez.
 * Esta tabla guarda hasta que tramo ha cobrado cada usuario en cada membresia, para
 * pagar solo cuando sube de tramo y solo la diferencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expansion_bonus_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_type_id');
            $table->unsignedTinyInteger('tier');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'account_type_id'], 'expansion_bonus_user_account_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expansion_bonus_payments');
    }
};
