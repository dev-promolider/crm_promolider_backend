<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FrequentQuestion;

class frequentQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FrequentQuestion::create([
            'id' => 1,
            'question'=> '¿Recibo una constancia de mi aprendizaje en el aula virtual de Promolíder?',
            'answer'=> 'Al finalizar el curso el estudiante recibirá un certificado un certificado avalado por el administrador del aula virtual y el productor del curso.',
            'status' => 1
        ]);
        FrequentQuestion::create([
            'id' => 2,
            'question'=> '¿Dónde se guardan mis cursos comprados en el aula virtual?',
            'answer'=> 'Sus cursos pueden ser observados ingresando en la opción de “Mi aprendizaje” que se encuentra en la barra de navegación lateral del aula virtual.',
            'status' => 1
        ]);
        FrequentQuestion::create([
            'id' => 3,
            'question'=> '¿Cuánto durará esta promoción?',
            'answer'=> 'Esta promoción por tiempo limitado estará disponible solo desde el jueves 17 de junio a las 00:00 UTC hasta el jueves 31 de julio a las 14:00 UTC.',
            'status' => 1
        ]);
        FrequentQuestion::create([
            'id' => 4,
            'question'=> '¿Cuáles son las formas de pago?',
            'answer'=> 'Compre los cursos con su tarjeta de crédito o débito: ¡nuestra opción más fácil y rápida!',
            'status' => 1
        ]);
        FrequentQuestion::create([
            'id' => 5,
            'question'=> '¿Dónde puedo resolver mis dudas sobre la clase de un curso?',
            'answer'=> 'En cada clase existe una sección de comentarios que se encuentra la parte inferior de la página donde se pueden realizar preguntas al productor del curso.',
            'status' => 1
        ]);

    }
}
