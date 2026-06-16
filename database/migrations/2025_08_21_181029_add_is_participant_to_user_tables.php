<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsParticipantToUserTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar isParticipant a masterclass_user
        Schema::table('masterclass_user', function (Blueprint $table) {
            $table->boolean('isParticipant')->default(false)->after('age');
        });

        // Agregar isParticipant a mini_course_users
        Schema::table('mini_course_users', function (Blueprint $table) {
            $table->boolean('isParticipant')->default(false)->after('last_accessed_at');
        });

        // Agregar isParticipant a ebook_users
        Schema::table('ebook_users', function (Blueprint $table) {
            $table->boolean('isParticipant')->default(false)->after('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar isParticipant de masterclass_user
        Schema::table('masterclass_user', function (Blueprint $table) {
            $table->dropColumn('isParticipant');
        });

        // Eliminar isParticipant de mini_course_users
        Schema::table('mini_course_users', function (Blueprint $table) {
            $table->dropColumn('isParticipant');
        });

        // Eliminar isParticipant de ebook_users
        Schema::table('ebook_users', function (Blueprint $table) {
            $table->dropColumn('isParticipant');
        });
    }
}
