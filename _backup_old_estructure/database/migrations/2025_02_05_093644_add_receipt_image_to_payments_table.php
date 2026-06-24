<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptImageToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('payments', function (Blueprint $table) {
    if (!Schema::hasColumn('payments', 'receipt_image')) {
        $table->string('receipt_image')->nullable();
    }
});

    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('receipt_image'); // Elimina la columna si la migración se revierte
        });
    }
}
