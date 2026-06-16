<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('dinamicas', 'tipo_dinamica')) {
            Schema::table('dinamicas', function (Blueprint $table) {
                $table->string('tipo_dinamica')->default('ruleta')->after('nombre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dinamicas', 'tipo_dinamica')) {
            Schema::table('dinamicas', function (Blueprint $table) {
                $table->dropColumn('tipo_dinamica');
            });
        }
    }
};
