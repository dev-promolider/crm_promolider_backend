<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('masterclasses', function (Blueprint $table) {
            $table->string('landing_banner')->nullable()->after('description');
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->string('landing_banner')->nullable()->after('description');
        });

        Schema::table('mini_courses', function (Blueprint $table) {
            $table->string('landing_banner')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masterclasses', function (Blueprint $table) {
            $table->dropColumn('landing_banner');
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn('landing_banner');
        });

        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropColumn('landing_banner');
        });
    }
};
