<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name');
            $table->string('last_name');
            $table->date('date_birth');
            $table->string('phone');
            $table->string('city')->nullable();
            $table->string('nro_document')->unique();
            $table->string('photo')->nullable();
            $table->text('biography')->nullable();
            $table->text('timezone')->nullable();
            $table->string('status_preference')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_approved')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token')->nullable();
            $table->unsignedBigInteger('id_country');
            $table->unsignedBigInteger('id_document_type');
            $table->unsignedBigInteger('id_referrer_sponsor')->nullable();
            $table->decimal('credits', 10, 2)->default(0.00);
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_country')->references('id')->on('country');
            $table->foreign('id_document_type')->references('id')->on('document_type');
            $table->foreign('id_referrer_sponsor')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
