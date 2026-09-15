<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que se pago por un curso y si ya se repartieron sus premios.
 *
 * En el sistema nuevo comprar un curso no generaba PV ni pagaba comisiones. Al
 * conectarlo, el reparto tiene que ocurrir una sola vez por compra aunque la
 * confirmacion de Openpay llegue dos veces; esta marca lo garantiza.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchased_courses', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->nullable()->after('course_id');
            $table->timestamp('rewards_distributed_at')->nullable()->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('purchased_courses', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'rewards_distributed_at']);
        });
    }
};
