<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIdCountryColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('city');
            $table->foreign('id_country')
                    ->references('id')
                    ->on('country')
                    ->after('id');
            $table->foreign('id_document_type')
                    ->references('id')
                    ->on('document_type')
                    ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('city');
            $table->dropForeign(['id_country']);
            $table->dropForeign(['id_document_type']);
        });
    }
}
