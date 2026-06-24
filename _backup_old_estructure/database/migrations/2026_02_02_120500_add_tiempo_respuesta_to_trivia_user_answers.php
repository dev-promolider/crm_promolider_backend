<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('trivia_user_answers')) {
            return;
        }

        Schema::table('trivia_user_answers', function (Blueprint $table) {
            if (! Schema::hasColumn('trivia_user_answers', 'tiempo_respuesta')) {
                $table->unsignedDecimal('tiempo_respuesta', 8, 2)
                    ->default(0)
                    ->after('puntos_obtenidos');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('trivia_user_answers')) {
            return;
        }

        Schema::table('trivia_user_answers', function (Blueprint $table) {
            if (Schema::hasColumn('trivia_user_answers', 'tiempo_respuesta')) {
                $table->dropColumn('tiempo_respuesta');
            }
        });
    }
};
