<?php

namespace Tests\Feature\Preregistro;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

trait SetupPreregistroTables
{
    protected function setUpPreregistroTables(): void
    {
        $this->createUsersTable();
        $this->createPreregistrosTable();
        $this->createPreregistroLinksTable();
        $this->createUnverifiedUsersTable();
        $this->createCountryTable();
        $this->createDocumentTypeTable();
        $this->createOpenpayOrderTable();
        $this->createAccountTypeTable();
    }

    private function createUsersTable(): void
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
            $table->bigInteger('id_country')->unsigned()->default(1);
            $table->bigInteger('id_document_type')->unsigned()->default(1);
            $table->string('nro_document', 18)->unique();
            $table->bigInteger('id_account_type')->unsigned()->default(1);
            $table->integer('id_referrer_sponsor')->default(0);
            $table->string('request', 1)->default('0');
            $table->string('expiration_date')->nullable();
            $table->string('position', 1)->default('0');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    private function createPreregistrosTable(): void
    {
        Schema::create('preregistros', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo')->unique();
            $table->string('whatsapp');
            $table->string('referrer_username')->nullable();
            $table->enum('lado', ['izquierda', 'derecha'])->nullable();
            $table->timestamps();
        });
    }

    private function createPreregistroLinksTable(): void
    {
        Schema::create('preregistro_links', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->enum('lado', ['izquierda', 'derecha'])->default('izquierda');
            $table->enum('landing', ['claro', 'oscuro'])->default('claro');
            $table->timestamps();
        });
    }

    private function createUnverifiedUsersTable(): void
    {
        Schema::create('unverified_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('openpay_order_id');
            $table->json('data');
            $table->timestamps();
        });
    }

    private function createCountryTable(): void
    {
        Schema::create('country', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        $country = new \App\Models\Country();
        $country->name = 'Perú';
        $country->save();
    }

    private function createDocumentTypeTable(): void
    {
        Schema::create('document_type', function (Blueprint $table) {
            $table->id();
            $table->string('document', 25);
            $table->timestamps();
        });

        $docType = new \App\Models\DocumentType();
        $docType->document = 'DNI';
        $docType->save();
    }

    private function createOpenpayOrderTable(): void
    {
        Schema::create('openpay_order', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->timestamps();
        });

        \App\Models\OpenpayOrder::create(['value' => '1000']);
    }

    private function createAccountTypeTable(): void
    {
        Schema::create('account_type', function (Blueprint $table) {
            $table->id();
            $table->string('account', 50);
            $table->string('price', 20)->default('0');
            $table->string('iva', 10)->default('0');
            $table->string('status', 1)->default('1');
            $table->timestamps();
        });

        \App\Models\AccountType::create([
            'account' => 'Guest',
            'price'   => '53.10',
            'iva'     => '18',
            'status'  => '1',
        ]);
    }
}
