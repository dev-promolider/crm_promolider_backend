<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToDinamicasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dinamicas', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('is_public');
            $table->timestamp('activated_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dinamicas', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'activated_at']);
        });
    }
}
