<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Catálogos Base
        $countryId = DB::table('country')->insertGetId([
            'name' => 'Perú',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $docTypeId = DB::table('document_type')->insertGetId([
            'document' => 'DNI',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $accountTypeId = DB::table('account_type')->insertGetId([
            'account' => 'Membresía VIP',
            'price' => 100.00,
            'status' => '1',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $paymentMethodId = DB::table('payment_method')->insertGetId([
            'name' => 'PayPal',
            'status' => '1',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $bonusTypeId = DB::table('bonus_type')->insertGetId([
            'description' => 'Bono de Inicio Rápido',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $rankId = DB::table('rank_bonus')->insertGetId([
            'name' => 'Rango Plata',
            'vol_min' => 1000.00,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // 2. Roles
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Administrador',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // 3. Usuarios
        // Usuario 1 (Patrocinador)
        $sponsorId = DB::table('users')->insertGetId([
            'username' => 'admin_sponsor',
            'email' => 'admin@promolider.test',
            'password' => Hash::make('password123'),
            'name' => 'Admin',
            'last_name' => 'Sponsor',
            'date_birth' => '1990-01-01',
            'phone' => '+51999999999',
            'nro_document' => '12345678',
            'is_active' => 1,
            'is_approved' => 1,
            'id_country' => $countryId,
            'id_document_type' => $docTypeId,
            'credits' => 50.00,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Usuario 2 (Hijo)
        $userId = DB::table('users')->insertGetId([
            'username' => 'test_user',
            'email' => 'user@promolider.test',
            'password' => Hash::make('password123'),
            'name' => 'Test',
            'last_name' => 'User',
            'date_birth' => '1995-05-05',
            'phone' => '+51999999998',
            'nro_document' => '87654321',
            'is_active' => 1,
            'is_approved' => 1,
            'id_country' => $countryId,
            'id_document_type' => $docTypeId,
            'id_referrer_sponsor' => $sponsorId,
            'credits' => 10.00,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Asignar Rol al Admin
        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => 'App\Models\User',
            'model_id' => $sponsorId
        ]);

        // 4. Membresía y Finanzas
        DB::table('account_type_details')->insert([
            'user_id' => $userId,
            'account_type_id' => $accountTypeId,
            'purchase_date' => $now,
            'expiration_date' => $now->copy()->addYear(),
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $walletSponsorId = DB::table('wallet')->insertGetId([
            'id_user' => $sponsorId,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $walletUserId = DB::table('wallet')->insertGetId([
            'id_user' => $userId,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Movimiento de prueba
        DB::table('wallet_movements')->insert([
            'wallet_id' => $walletSponsorId,
            'amount' => 100.00,
            'type' => 1, // Ingreso
            'bonus_type_id' => $bonusTypeId,
            'reason' => 'Bono de bienvenida',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Pagos
        DB::table('payments')->insert([
            'user_id' => $userId,
            'payment_method_id' => $paymentMethodId,
            'amount' => 100.00,
            'operation_number' => 'OP-12345',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // 5. Red Binaria
        DB::table('binary_tree')->insert([
            'user_id' => $userId,
            'binary_sponsor' => $sponsorId,
            'position' => 'L',
            'user_above' => $sponsorId,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('points')->insert([
            'user_id' => $userId,
            'sponsor_id' => $sponsorId,
            'points_val' => 100.00,
            'side' => 0, // Izquierda
            'reason' => 'Ingreso de paquete',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // 6. Aula Virtual
        $courseId = DB::table('courses')->insertGetId([
            'product_type_id' => 1,
            'user_id' => $sponsorId,
            'id_categories' => 1,
            'title' => 'Curso de Liderazgo',
            'description' => 'Aprende a ser un líder efectivo.',
            'price' => 50.00,
            'portada' => 'portada.jpg',
            'url_portada' => 'https://ejemplo.com/portada.jpg',
            'course_about' => 'Acerca de...',
            'will_learn' => 'Aprenderás...',
            'prev_knowledge' => 'Ninguno',
            'course_for' => 'Todos',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $moduleId = DB::table('modules')->insertGetId([
            'course_id' => $courseId,
            'name' => 'Módulo 1: Introducción',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $lessonId = DB::table('lessons')->insertGetId([
            'module_id' => $moduleId,
            'name' => 'Lección 1.1',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $purchasedCourseId = DB::table('purchased_courses')->insertGetId([
            'user_id' => $userId,
            'course_id' => $courseId,
            'progress' => 50.00,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        DB::table('lesson_progress')->insert([
            'purchased_course_id' => $purchasedCourseId,
            'lesson_id' => $lessonId,
            'is_completed' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}
