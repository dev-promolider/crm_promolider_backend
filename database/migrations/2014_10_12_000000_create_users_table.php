<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('name');
            $table->string('last_name', 50);
            $table->date('date_birth');
            $table->string('phone', 16);
            $table->bigInteger('id_country')->unsigned();
            $table->bigInteger('id_document_type')->unsigned();
            $table->string('nro_document', 18)->unique();
            $table->bigInteger('id_account_type')->unsigned();
            $table->integer('id_referrer_sponsor');
            $table->string('request', 1)->default('0');
            $table->string('expiration_date')->nullable();
            $table->string('position', 1)->default('0');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
