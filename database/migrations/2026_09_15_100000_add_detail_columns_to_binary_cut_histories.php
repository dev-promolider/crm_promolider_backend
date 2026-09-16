<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El detalle de por que cobro cada ganador del corte.
 *
 * Tras el primer corte real (lote 88) el ingeniero pidio revisar "exactamente que
 * calculo", y el historial solo guardaba las dos piernas y el importe. Con estas
 * columnas la vista de ganadores puede explicar el pago sin reconstruirlo con la
 * configuracion de hoy, que puede haber cambiado desde entonces.
 *
 * Son nulas: los cortes anteriores no las tienen y la vista lo indica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('binary_cut_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('account_type_id')->nullable()->after('rank_id');
            $table->decimal('pay_percentage', 5, 2)->nullable()->after('right_points');
            $table->decimal('calculated_amount', 12, 2)->nullable()->after('pay_percentage');
            $table->boolean('capped')->default(false)->after('calculated_amount');
            $table->decimal('carryover_points', 12, 2)->nullable()->after('transferred_amount');
            $table->tinyInteger('carryover_side')->nullable()->after('carryover_points');
        });
    }

    public function down(): void
    {
        Schema::table('binary_cut_histories', function (Blueprint $table) {
            $table->dropColumn([
                'account_type_id',
                'pay_percentage',
                'calculated_amount',
                'capped',
                'carryover_points',
                'carryover_side',
            ]);
        });
    }
};
