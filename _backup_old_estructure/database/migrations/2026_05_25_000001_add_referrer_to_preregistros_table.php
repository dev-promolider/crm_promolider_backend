<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preregistros', function (Blueprint $table) {
            $table->string('referrer_username')->nullable()->after('whatsapp');
            $table->enum('lado', ['izquierda', 'derecha'])->nullable()->after('referrer_username');
        });
    }

    public function down(): void
    {
        Schema::table('preregistros', function (Blueprint $table) {
            $table->dropColumn(['referrer_username', 'lado']);
        });
    }
};
