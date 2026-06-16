<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowTablesAndColumns extends Command
{
    protected $signature = 'db:show-structure {--simple : Mostrar solo tipos básicos}';
    protected $description = 'Muestra las tablas y sus columnas con sus tipos de datos';

    public function handle()
    {
        $database = config('database.connections.' . config('database.default') . '.database');
        $simple = $this->option('simple');

        $this->info("Base de datos: $database");
        $this->line('');

        // Obtener todas las tablas
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . $database;

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $this->info("Tabla: $tableName");

            try {
                // Usar DESCRIBE para obtener información detallada de las columnas
                $columns = DB::select("DESCRIBE `$tableName`");
                
                foreach ($columns as $column) {
                    if ($simple) {
                        $type = $this->simplifyType($column->Type);
                        $this->line("   - {$column->Field} ($type)");
                    } else {
                        $columnInfo = $this->formatColumnInfo($column);
                        $this->line("   - {$column->Field} ({$columnInfo})");
                    }
                }
            } catch (\Exception $e) {
                $this->error("   Error al obtener columnas para la tabla $tableName: " . $e->getMessage());
                
                // Intentar método alternativo
                try {
                    $this->warn("   Intentando método alternativo...");
                    $columns = DB::select("SELECT COLUMN_NAME, DATA_TYPE 
                                         FROM INFORMATION_SCHEMA.COLUMNS 
                                         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", 
                                         [$database, $tableName]);
                    
                    foreach ($columns as $column) {
                        $this->line("   - {$column->COLUMN_NAME} ({$column->DATA_TYPE})");
                    }
                } catch (\Exception $e2) {
                    $this->error("   No se pudo obtener información de la tabla: " . $e2->getMessage());
                }
            }

            $this->line('');
        }

        $this->info("Estructura de base de datos generada exitosamente.");
    }

    /**
     * Formatea la información de la columna
     */
    private function formatColumnInfo($column)
    {
        $type = $column->Type;
        
        // Simplificar algunos tipos comunes
        $type = $this->simplifyType($type);
        
        $info = $type;
        
        // Agregar información adicional si es relevante
        if ($column->Null === 'NO') {
            $info .= ', NOT NULL';
        }
        
        if ($column->Key === 'PRI') {
            $info .= ', PRIMARY KEY';
        } elseif ($column->Key === 'UNI') {
            $info .= ', UNIQUE';
        } elseif ($column->Key === 'MUL') {
            $info .= ', INDEX';
        }
        
        if ($column->Extra) {
            $info .= ', ' . strtoupper($column->Extra);
        }
        
        if ($column->Default !== null) {
            $info .= ", DEFAULT: {$column->Default}";
        }
        
        return $info;
    }

    /**
     * Simplifica los tipos de datos para mejor legibilidad
     */
    private function simplifyType($type)
    {
        // Mapear tipos comunes a nombres más simples
        $typeMapping = [
            '/^bigint\(\d+\)/' => 'bigint',
            '/^int\(\d+\)/' => 'integer',
            '/^smallint\(\d+\)/' => 'smallint',
            '/^tinyint\(\d+\)/' => 'tinyint',
            '/^varchar\(\d+\)/' => 'string',
            '/^char\(\d+\)/' => 'char',
            '/^text$/' => 'text',
            '/^longtext$/' => 'text',
            '/^mediumtext$/' => 'text',
            '/^datetime$/' => 'datetime',
            '/^timestamp$/' => 'timestamp',
            '/^date$/' => 'date',
            '/^time$/' => 'time',
            '/^decimal\(\d+,\d+\)/' => 'decimal',
            '/^float$/' => 'float',
            '/^double$/' => 'double',
            '/^boolean$/' => 'boolean',
            '/^tinyint\(1\)$/' => 'boolean',
        ];

        foreach ($typeMapping as $pattern => $replacement) {
            if (preg_match($pattern, $type)) {
                return $replacement;
            }
        }

        // Si es un ENUM, mantenerlo como está pero simplificado
        if (strpos($type, 'enum') === 0) {
            return 'enum';
        }

        return $type;
    }
}