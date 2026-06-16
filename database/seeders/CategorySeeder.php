<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            'name' => 'Desarrollo Web',
            'icon' => 'fas fa-code',
        ]);
        DB::table('categories')->insert([
            'name' => 'Negocio',
            'icon' => 'fas fa-briefcase',
        ]);
        DB::table('categories')->insert([
            'name' => 'Desarrollo Personal',
            'icon' => 'fas fa-child',
        ]);
        DB::table('categories')->insert([
            'name' => 'Diseño',
            'icon' => 'fas fa-pencil-ruler',
        ]);
        DB::table('categories')->insert([
            'name' => 'Marketing',
            'icon' => 'fas fa-mail-bulk',
        ]);
        DB::table('categories')->insert([
            'name' => 'Estilo de vida',
            'icon' => 'fas fa-spa',
        ]);
        DB::table('categories')->insert([
            'name' => 'Salud y Fitness',
            'icon' => 'fas fa-heartbeat',
        ]);
        DB::table('categories')->insert([
            'name' => 'Enseñanza y academia',
            'icon' => 'fas fa-user-graduate',
        ]);
        DB::table('categories')->insert([
            'name' => 'Aplicaciones Móviles',
            'icon' => 'fas fa-mobile-alt',
        ]);
        DB::table('categories')->insert([
            'name' => 'Lenguajes de Programación',
            'icon' => 'fas fa-file-code',
        ]);
        DB::table('categories')->insert([
            'name' => 'Desarrollo de juegos',
            'icon' => 'fas fa-gamepad',
        ]);
        DB::table('categories')->insert([
            'name' => 'Finanzas',
            'icon' => 'fas fa-piggy-bank',
        ]);
        DB::table('categories')->insert([
            'name' => 'Comunicaciones',
            'icon' => 'fas fa-broadcast-tower',
        ]);
        DB::table('categories')->insert([
            'name' => 'Estrategia',
            'icon' => 'fas fa-sitemap',
        ]);
        DB::table('categories')->insert([
            'name' => 'Gestión de proyectos',
            'icon' => 'fas fa-chart-line',
        ]);
        DB::table('categories')->insert([
            'name' => 'Derecho Mercantil',
            'icon' => 'fas fa-balance-scale-left',
        ]);
        DB::table('categories')->insert([
            'name' => 'Transformación Personal',
            'icon' => 'fas fa-street-view',
        ]);
        DB::table('categories')->insert([
            'name' => 'Liderazgo',
            'icon' => 'fas fa-chalkboard-teacher',
        ]);
        DB::table('categories')->insert([
            'name' => 'Diseño Web',
            'icon' => 'fas fa-laptop-code',
        ]);
    }
}
