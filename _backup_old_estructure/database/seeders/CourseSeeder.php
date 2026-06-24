<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = DB::table('users')->pluck('id');
        $categories = DB::table('categories')->pluck('id');

        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'curso de python',
            'description' => 'descripcion del curso',
            'portada' => '840_560.jpg',
            'url_portada' => 'courses/1/1/portada/840_560.jpg',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 0,
            'course_level_id' => 2,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'curso de java',
            'description' => 'descripcion del curso',
            'portada' => 'java_portada.jpg',
            'url_portada' => 'courses/1/2/portada/java_portada.jpg',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 0,
            'course_level_id' => 3,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'aprende desarrollo web desde cero',
            'description' => 'descripcion del curso',
            'portada' => 'descargar.png',
            'url_portada' => 'courses/1/3/portada/descargar.png',
            'price' => 0,
            'ranking_by_user' => 3,
            'status' => 0,
            'course_level_id' => 1,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'curso de machine learning',
            'description' => 'descripcion del curso',
            'portada' => 'ml.jpg',
            'url_portada' => 'courses/1/4/portada/ml.jpg',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 0,
            'course_level_id' => 3,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'laravel desde cero',
            'description' => 'descripcion del curso',
            'portada' => 'opengraph_fundamentos_laravel.png',
            'url_portada' => 'courses/1/5/portada/opengraph_fundamentos_laravel.png',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 0,
            'course_level_id' => 2,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'power bi de cero a master',
            'description' => 'descripcion del curso',
            'portada' => 'curso-power-bi-1024x581.jpg',
            'url_portada' => 'courses/1/6/portada/curso-power-bi-1024x581.jpg',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 2,
            'course_level_id' => 2,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'curso de rubi',
            'description' => 'descripcion del curso',
            'portada' => 'maxresdefault.jpg',
            'url_portada' => 'courses/1/7/portada/maxresdefault.jpg',
            'price' => 0,
            'ranking_by_user' => 3,
            'status' => 2,
            'course_level_id' => 1,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'java para principiantes',
            'description' => 'descripcion del curso',
            'portada' => 'java_portada.jpg',
            'url_portada' => 'courses/1/8/portada/java_portada.jpg',
            'price' => 0,
            'ranking_by_user' => 3,
            'status' => 2,
            'course_level_id' => 1,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'Curso de Express',
            'description' => 'descripcion del curso',
            'portada' => 'ef0d92b3-74d6-4bec-bc4f-baa18dcf558e.png',
            'url_portada' => 'courses/1/9/portada/ef0d92b3-74d6-4bec-bc4f-baa18dcf558e.png',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 2,
            'course_level_id' => 2,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        DB::table('courses')->insert([
            'user_id' => $users->random(),
            'id_categories' => $categories->random(),
            'title' => 'Curso de Ionic',
            'description' => 'descripcion del curso',
            'portada' => 'opengraph_curso_ionic.png',
            'url_portada' => 'courses/1/10/portada/opengraph_curso_ionic.png',
            'price' => 250,
            'ranking_by_user' => 3,
            'status' => 2,
            'course_level_id' => 2,
            'course_about' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'will_learn' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'prev_knowledge' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'course_for' => 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consectetur minima nisi rem praesentium inventore dignissimos in alias laboriosam tempora illum nemo perferendis nam sint et, omnis quae? Voluptatibus, sequi incidunt?
            Neque sint ratione ab, atque deleniti voluptates pariatur possimus, accusamus asperiores mollitia dolores quidem! Quaerat enim, veritatis vel a totam aliquam, facilis odit, debitis voluptas nihil eligendi dignissimos dolorum fuga.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
    }
}
