<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterclassUserTable extends Migration
{
    public function up(): void
    {
        Schema::table('masterclass_user', function (Blueprint $table) {
            if (!Schema::hasColumn('masterclass_user', 'user_type')) {
                $table->enum('user_type', ['Guest', 'Affiliate'])
                      ->default('Guest')
                      ->after('nationality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('masterclass_user', function (Blueprint $table) {
            if (Schema::hasColumn('masterclass_user', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
}
