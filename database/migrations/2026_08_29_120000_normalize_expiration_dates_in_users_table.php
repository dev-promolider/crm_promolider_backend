<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las columnas expiration_date y expiration_membership_date son varchar y traen
 * valores mezclados: fechas 'Y-m-d H:i:s' del sistema nuevo y marcas de tiempo UNIX
 * que escribe el monolito (UserController y AuthController siguen guardando
 * strtotime() en crudo).
 *
 * Carbon::parse revienta con las segundas, y eso era lo que tumbaba el corte binario
 * entero antes de pagar a nadie. Esta migracion normaliza lo que ya esta guardado.
 *
 * No se cambia el tipo de la columna a datetime a proposito: mientras el monolito
 * siga escribiendo en esta misma base, un datetime rechazaria sus escrituras. El
 * sistema nuevo se protege ademas con User::parseExpiration().
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['expiration_date', 'expiration_membership_date'] as $columna) {
            // Marcas de tiempo UNIX -> fecha legible
            DB::statement("
                UPDATE users
                SET {$columna} = DATE_FORMAT(FROM_UNIXTIME({$columna}), '%Y-%m-%d %H:%i:%s')
                WHERE {$columna} REGEXP '^[0-9]+$'
            ");

            // Cadenas vacias -> NULL, para que no se interpreten como fecha invalida
            DB::statement("
                UPDATE users
                SET {$columna} = NULL
                WHERE {$columna} = ''
            ");
        }
    }

    public function down(): void
    {
        // No hay vuelta atras: no se guarda cual era el formato original de cada fila.
        // Tampoco hace falta, el formato de fecha es valido para ambos sistemas.
    }
};
