<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Un tipo de bono propio para el bono mensual de rango.
 *
 * En bonus_type el id 5 se llama "Bono de rangos", pero los 103 movimientos que
 * tiene son todos del bono generacional ("Bono de 1° Generación" y siguientes): es
 * la etiqueta la que esta mal, no los datos. Se deja como esta para no romper nada
 * que la lea, y el bono mensual de rango —que hasta ahora no se pagaba nunca— entra
 * con identificador propio.
 */
return new class extends Migration
{
    public function up(): void
    {
        $existe = DB::table('bonus_type')->where('description', 'Bono mensual de rango')->exists();

        if (!$existe) {
            DB::table('bonus_type')->insert([
                'id'          => 7,
                'description' => 'Bono mensual de rango',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('bonus_type')->where('description', 'Bono mensual de rango')->delete();
    }
};
