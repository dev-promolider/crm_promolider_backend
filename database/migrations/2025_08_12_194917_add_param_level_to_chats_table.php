<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParamLevelToChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {
            if(!Schema::hasColumn('chats','param')){
                $table->tinyInteger('param')->default(1)->after('status');
            }
            if(!Schema::hasColumn('chats','level')){
                $table->tinyInteger('level')->default(1)->after('param');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['param','level']);
        });
    }
}
