<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToEditTemplateTable extends Migration
{
    public function up()
    {
        Schema::table('edit_template', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('title');
        });
    }

    public function down()
    {
        Schema::table('edit_template', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
