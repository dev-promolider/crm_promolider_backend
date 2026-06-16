<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamCourseQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('exam_question')->insert([
            'exam_id' => 4,
            'title' => '¿Que es ionic??',
            'points' => 25,
            'type' => 1,
            'options' => '["Es un SDK de frontend","Es un framework","Es un lenguaje de backend","Es una base de datos","Es una herramienta para codificar"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 4,
            'title' => 'Principal característica de ionic.',
            'points' => 25,
            'type' => 1,
            'options' => '["Permite desarrollar y desplegar aplicaciones hybridas, que funcionan en multiples plataformas","Permite desarrollar codigo backend","Permite desarrollar CSS","Permite desarrollar APIs","Permite desarrollar autorizacion por tokens"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 4,
            'title' => 'Quien desarrollo ionic?',
            'points' => 25,
            'type' => 1,
            'options' => '["Drifty","Apache","Linus Trovalds","Bill Gates","Mackintosh"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 4,
            'title' => 'Que se necesita para instalar ionic?',
            'points' => 25,
            'type' => 1,
            'options' => '["Modulo npm de node js","Php","Python","Java","Css"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);


        DB::table('exam_question')->insert([
            'exam_id' => 5,
            'title' => '¿Que es ionic??',
            'points' => 25,
            'type' => 1,
            'options' => '["Es un SDK de frontend","Es un framework","Es un lenguaje de backend","Es una base de datos","Es una herramienta para codificar"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 5,
            'title' => 'Principal característica de ionic.',
            'points' => 25,
            'type' => 1,
            'options' => '["Permite desarrollar y desplegar aplicaciones hybridas, que funcionan en multiples plataformas","Permite desarrollar codigo backend","Permite desarrollar CSS","Permite desarrollar APIs","Permite desarrollar autorizacion por tokens"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 5,
            'title' => 'Quien desarrollo ionic?',
            'points' => 25,
            'type' => 1,
            'options' => '["Drifty","Apache","Linus Trovalds","Bill Gates","Mackintosh"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 5,
            'title' => 'Que se necesita para instalar ionic?',
            'points' => 25,
            'type' => 1,
            'options' => '["Modulo npm de node js","Php","Python","Java","Css"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 3,
            'title' => '¿Que es ionic??',
            'points' => 25,
            'type' => 1,
            'options' => '["Es un SDK de frontend","Es un framework","Es un lenguaje de backend","Es una base de datos","Es una herramienta para codificar"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 3,
            'title' => 'Principal característica de ionic.',
            'points' => 25,
            'type' => 1,
            'options' => '["Permite desarrollar y desplegar aplicaciones hybridas, que funcionan en multiples plataformas","Permite desarrollar codigo backend","Permite desarrollar CSS","Permite desarrollar APIs","Permite desarrollar autorizacion por tokens"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 3,
            'title' => 'Quien desarrollo ionic?',
            'points' => 25,
            'type' => 1,
            'options' => '["Drifty","Apache","Linus Trovalds","Bill Gates","Mackintosh"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 3,
            'title' => 'Que se necesita para instalar ionic?',
            'points' => 25,
            'type' => 1,
            'options' => '["Modulo npm de node js","Php","Python","Java","Css"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 6,
            'title' => 'Que es Express?',
            'points' => 25,
            'type' => 1,
            'options' => '["Framework web m\u00e1s popular de Node","Framework de php","Framework de java","Framework de python","Framework de css"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 6,
            'title' => 'Principal característica de express',
            'points' => 25,
            'type' => 1,
            'options' => '["Ha sido dise\u00f1ado para optimizar el rendimiento y la escalabilidad","Ha sido dise\u00f1ado para optimizar consultas SQL","Ha sido dise\u00f1ado para crear complejos sistemas de rutas","Ha sido dise\u00f1ado para levantar un servidor","Ha sido dise\u00f1ado para poder ejecutar un contenedor docker"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 6,
            'title' => 'Quien desarrollo express?',
            'points' => 25,
            'type' => 1,
            'options' => '["TJ Holowaychuk","Microsoft","Linux","Apache","Oracle"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 6,
            'title' => 'Que se necesita para instalar express?',
            'points' => 25,
            'type' => 1,
            'options' => '["Npm","Servidor","Apache tomcat","Linux","Windows"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 7,
            'title' => 'Que es Express?',
            'points' => 25,
            'type' => 1,
            'options' => '["Framework web m\u00e1s popular de Node","Framework de php","Framework de java","Framework de python","Framework de css"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 7,
            'title' => 'Principal característica de express',
            'points' => 25,
            'type' => 1,
            'options' => '["Ha sido dise\u00f1ado para optimizar el rendimiento y la escalabilidad","Ha sido dise\u00f1ado para optimizar consultas SQL","Ha sido dise\u00f1ado para crear complejos sistemas de rutas","Ha sido dise\u00f1ado para levantar un servidor","Ha sido dise\u00f1ado para poder ejecutar un contenedor docker"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 7,
            'title' => 'Quien desarrollo express?',
            'points' => 25,
            'type' => 1,
            'options' => '["TJ Holowaychuk","Microsoft","Linux","Apache","Oracle"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 7,
            'title' => 'Que se necesita para instalar express?',
            'points' => 25,
            'type' => 1,
            'options' => '["Npm","Servidor","Apache tomcat","Linux","Windows"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 8,
            'title' => 'Que es Express?',
            'points' => 25,
            'type' => 1,
            'options' => '["Framework web m\u00e1s popular de Node","Framework de php","Framework de java","Framework de python","Framework de css"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 8,
            'title' => 'Principal característica de express',
            'points' => 25,
            'type' => 1,
            'options' => '["Ha sido dise\u00f1ado para optimizar el rendimiento y la escalabilidad","Ha sido dise\u00f1ado para optimizar consultas SQL","Ha sido dise\u00f1ado para crear complejos sistemas de rutas","Ha sido dise\u00f1ado para levantar un servidor","Ha sido dise\u00f1ado para poder ejecutar un contenedor docker"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 8,
            'title' => 'Quien desarrollo express?',
            'points' => 25,
            'type' => 1,
            'options' => '["TJ Holowaychuk","Microsoft","Linux","Apache","Oracle"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 8,
            'title' => 'Que se necesita para instalar express?',
            'points' => 25,
            'type' => 1,
            'options' => '["Npm","Servidor","Apache tomcat","Linux","Windows"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 9,
            'title' => 'Que es Java?',
            'points' => 25,
            'type' => 1,
            'options' => '["Un lenguaje de programacion backend","Un lenguaje de programacion frontend","Una libreria","Un framework","Un gestor de APIs"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 9,
            'title' => 'Principal característica de java',
            'points' => 25,
            'type' => 1,
            'options' => '["Lenguaje compilado","Es de bajo nivel","Tiene mayor complejidad que C","Es de paga","Esta obsoleto"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 9,
            'title' => 'Quien desarrollo java?',
            'points' => 25,
            'type' => 1,
            'options' => '["Sun Microsystems","Microsoft","Apache Fundation","Laravel","Spring"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 9,
            'title' => 'cual es el framework mas popular de java?',
            'points' => 25,
            'type' => 1,
            'options' => '["Springboot","Laravel","Express","Django","Angular"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 10,
            'title' => 'Que es Java?',
            'points' => 25,
            'type' => 1,
            'options' => '["Un lenguaje de programacion backend","Un lenguaje de programacion frontend","Una libreria","Un framework","Un gestor de APIs"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 10,
            'title' => 'Principal característica de java',
            'points' => 25,
            'type' => 1,
            'options' => '["Lenguaje compilado","Es de bajo nivel","Tiene mayor complejidad que C","Es de paga","Esta obsoleto"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 10,
            'title' => 'Quien desarrollo java?',
            'points' => 25,
            'type' => 1,
            'options' => '["Sun Microsystems","Microsoft","Apache Fundation","Laravel","Spring"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 10,
            'title' => 'cual es el framework mas popular de java?',
            'points' => 25,
            'type' => 1,
            'options' => '["Springboot","Laravel","Express","Django","Angular"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 11,
            'title' => 'Que es Power BI?',
            'points' => 25,
            'type' => 1,
            'options' => '["Software para el an\u00e1lisis de datos","programa para hacer Base de datos","Servidor","SSL","Framework"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 11,
            'title' => 'Principal característica de Power BI',
            'points' => 25,
            'type' => 1,
            'options' => '["Tiene una interfaz amigable y es de facil uso","Compila rapido","Tiene configuracion engorrosa","Tiene mucho delay en las consultas","Es muy pesado"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 11,
            'title' => 'Quien desarrollo Power BI?',
            'points' => 25,
            'type' => 1,
            'options' => '["Microsoft","Oracle","Apache Fundation","Sun Microsystems","Laravel"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 11,
            'title' => 'Que es ETL?',
            'points' => 25,
            'type' => 1,
            'options' => '["Extract , Transform and Load","Un software","Hardware","Lenguaje Maquina","Configuracion de servidor"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 12,
            'title' => 'Que es Power BI?',
            'points' => 25,
            'type' => 1,
            'options' => '["Software para el an\u00e1lisis de datos","programa para hacer Base de datos","Servidor","SSL","Framework"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 12,
            'title' => 'Principal característica de Power BI',
            'points' => 25,
            'type' => 1,
            'options' => '["Tiene una interfaz amigable y es de facil uso","Compila rapido","Tiene configuracion engorrosa","Tiene mucho delay en las consultas","Es muy pesado"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 12,
            'title' => 'Quien desarrollo Power BI?',
            'points' => 25,
            'type' => 1,
            'options' => '["Microsoft","Oracle","Apache Fundation","Sun Microsystems","Laravel"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 12,
            'title' => 'Que es ETL?',
            'points' => 25,
            'type' => 1,
            'options' => '["Extract , Transform and Load","Un software","Hardware","Lenguaje Maquina","Configuracion de servidor"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 13,
            'title' => 'Que es Power BI?',
            'points' => 25,
            'type' => 1,
            'options' => '["Software para el an\u00e1lisis de datos","programa para hacer Base de datos","Servidor","SSL","Framework"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 13,
            'title' => 'Principal característica de Power BI',
            'points' => 25,
            'type' => 1,
            'options' => '["Tiene una interfaz amigable y es de facil uso","Compila rapido","Tiene configuracion engorrosa","Tiene mucho delay en las consultas","Es muy pesado"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 13,
            'title' => 'Quien desarrollo Power BI?',
            'points' => 25,
            'type' => 1,
            'options' => '["Microsoft","Oracle","Apache Fundation","Sun Microsystems","Laravel"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 13,
            'title' => 'Que es ETL?',
            'points' => 25,
            'type' => 1,
            'options' => '["Extract , Transform and Load","Un software","Hardware","Lenguaje Maquina","Configuracion de servidor"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 14,
            'title' => 'Que es Rubi?',
            'points' => 25,
            'type' => 1,
            'options' => '["Un lenguaje de programaci\u00f3n","Un framework","Una libreria","Un servidor","Un contenedor"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 14,
            'title' => 'Principal característica de Ruby',
            'points' => 25,
            'type' => 1,
            'options' => '["Din\u00e1mico y de c\u00f3digo abierto","Poca documentacion","Privado","Sin soporte u obsoleto","Caro de mantener"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 14,
            'title' => 'Quien desarrollo Ruby?',
            'points' => 25,
            'type' => 1,
            'options' => '["Yukihiro Matsumoto","Microsoft","Oracle","Sun Microsystems","Apache Fundation"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 14,
            'title' => 'Framework popular de Ruby?',
            'points' => 25,
            'type' => 1,
            'options' => '["Cuba","Laravel","Django","Spring","Net"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 15,
            'title' => 'Que es Rubi?',
            'points' => 25,
            'type' => 1,
            'options' => '["Un lenguaje de programaci\u00f3n","Un framework","Una libreria","Un servidor","Un contenedor"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 15,
            'title' => 'Principal característica de Ruby',
            'points' => 25,
            'type' => 1,
            'options' => '["Din\u00e1mico y de c\u00f3digo abierto","Poca documentacion","Privado","Sin soporte u obsoleto","Caro de mantener"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 15,
            'title' => 'Quien desarrollo Ruby?',
            'points' => 25,
            'type' => 1,
            'options' => '["Yukihiro Matsumoto","Microsoft","Oracle","Sun Microsystems","Apache Fundation"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 15,
            'title' => 'Framework popular de Ruby?',
            'points' => 25,
            'type' => 1,
            'options' => '["Cuba","Laravel","Django","Spring","Net"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);

        DB::table('exam_question')->insert([
            'exam_id' => 16,
            'title' => 'Que es Rubi?',
            'points' => 25,
            'type' => 1,
            'options' => '["Un lenguaje de programaci\u00f3n","Un framework","Una libreria","Un servidor","Un contenedor"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 16,
            'title' => 'Principal característica de Ruby',
            'points' => 25,
            'type' => 1,
            'options' => '["Din\u00e1mico y de c\u00f3digo abierto","Poca documentacion","Privado","Sin soporte u obsoleto","Caro de mantener"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 16,
            'title' => 'Quien desarrollo Ruby?',
            'points' => 25,
            'type' => 1,
            'options' => '["Yukihiro Matsumoto","Microsoft","Oracle","Sun Microsystems","Apache Fundation"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
        DB::table('exam_question')->insert([
            'exam_id' => 16,
            'title' => 'Framework popular de Ruby?',
            'points' => 25,
            'type' => 1,
            'options' => '["Cuba","Laravel","Django","Spring","Net"]',
            'correct' => 0,
            'question_type_id' => 1,
        ]);
    }
}
