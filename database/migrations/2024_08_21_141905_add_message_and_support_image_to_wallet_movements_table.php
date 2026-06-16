<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageAndSupportImageToWalletMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_movements', function (Blueprint $table) {
            $table->text('message')->nullable(); // Campo 'message' de tipo texto
            $table->string('support_image')->nullable(); // Campo 'support_image' de tipo string
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
    {
        Schema::table('wallet_movements', function (Blueprint $table) {
            $table->dropColumn('message');
            $table->dropColumn('support_image');
        });
    }
}
