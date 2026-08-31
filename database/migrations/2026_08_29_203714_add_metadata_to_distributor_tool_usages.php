<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataToDistributorToolUsages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_tool_usages', function (Blueprint $table) {
            $table->string('generated_link')->nullable()->after('usageable_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_tool_usages', function (Blueprint $table) {
            $table->dropColumn('generated_link');
        });
    }
}
