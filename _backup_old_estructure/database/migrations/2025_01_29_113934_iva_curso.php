<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class IvaCurso extends Migration
{
    public function up()
    {
        DB::table('options')->insert([
            'description' => 'iva_rate',
            'value' => '18',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        DB::table('options')->where('description', 'iva_rate')->delete();
    }
}
