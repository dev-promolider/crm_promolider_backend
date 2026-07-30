<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('preregistro_links', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('lado');
            $table->string('landing')->default('oscuro');
            $table->timestamps();
        });

        Schema::create('preregistros', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo');
            $table->string('whatsapp');
            $table->string('referrer_username')->nullable();
            $table->string('lado')->nullable();
            $table->string('referrer_nombre')->nullable();
            $table->string('referrer_apellido')->nullable();
            $table->string('referrer_correo')->nullable();
            $table->string('referrer_whatsapp')->nullable();
            $table->string('url_invitacion', 500)->nullable();
            $table->string('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('unverified_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->longText('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unverified_users');
        Schema::dropIfExists('preregistros');
        Schema::dropIfExists('preregistro_links');
    }
};
