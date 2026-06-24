<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnDiscPurchasesToAccountTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_type', function (Blueprint $table) {
            $table->renameColumn('disc_purchases', 'disc_purchases_course');
            $table->unsignedDouble('disc_purchases_certificates',10,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('account_type', function (Blueprint $table) {
        //     $table->dropColumn('disc_purchases_course');
        //     $table->dropColumn('disc_purchases_certificates');
        // });
    }
}
