<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessTokenToMiniCourseUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mini_course_users', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->after('nationality');
            $table->timestamp('token_expires_at')->nullable()->after('access_token');
            $table->timestamp('last_accessed_at')->nullable()->after('token_expires_at');
            
            // Índice para mejorar el rendimiento de las consultas por token
            $table->index('access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mini_course_users', function (Blueprint $table) {
            $table->dropIndex(['access_token']);
            $table->dropColumn(['access_token', 'token_expires_at', 'last_accessed_at']);
        });
    }
}
