<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDetailsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('payments', 'details')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->text('details')->nullable();
            });
        }

        if (!Schema::hasColumn('wallet_movements', 'user_purchase_id')) {
            Schema::table('wallet_movements', function (Blueprint $table) {
                $table->bigInteger('user_purchase_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('payments', 'details')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('details');
            });
        }

        if (Schema::hasColumn('wallet_movements', 'user_purchase_id')) {
            Schema::table('wallet_movements', function (Blueprint $table) {
                $table->dropColumn('user_purchase_id');
            });
        }

    }
}
