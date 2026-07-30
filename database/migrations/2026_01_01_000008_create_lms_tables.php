<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_type_id'); // FK but maybe we don't have this table yet, will just leave as integer
            $table->string('instructor_signature_path')->nullable();
            $table->unsignedBigInteger('user_id'); // Instructor
            $table->unsignedBigInteger('id_categories'); // FK categories not present in UML, will leave as int
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('area')->nullable();
            $table->longText('description');
            $table->string('currency')->nullable();
            $table->double('price');
            $table->double('ranking_by_user')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('portada');
            $table->string('url_portada');
            $table->text('course_about');
            $table->text('will_learn');
            $table->text('prev_knowledge');
            $table->text('course_for');
            $table->integer('course_time')->default(0);
            $table->unsignedBigInteger('course_level_id')->nullable(); // no foreign table in UML
            $table->string('months')->nullable();
            $table->text('path_url')->nullable();
            $table->double('price_base')->nullable();
            $table->tinyInteger('certificate')->default(0);
            $table->unsignedBigInteger('certificate_template_id')->nullable();
            $table->tinyInteger('marketplace_listed')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('order_pos')->nullable();
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses');
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->time('time')->nullable();
            $table->longText('url')->nullable();
            $table->longText('description')->nullable();
            $table->char('status', 1)->nullable();
            $table->integer('order_pos')->nullable();
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules');
        });

        Schema::create('purchased_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->longText('classes_status')->nullable();
            $table->decimal('progress', 5, 2)->nullable();
            $table->string('last_class_reprod')->nullable();
            $table->tinyInteger('completed_course')->nullable();
            $table->date('completed_date')->nullable();
            $table->decimal('display_time', 10, 2)->nullable();
            $table->string('certificate_url')->nullable();
            $table->tinyInteger('certificate_delivered')->nullable();
            $table->tinyInteger('certificate_seen')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('course_id')->references('id')->on('courses');
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchased_course_id');
            $table->unsignedBigInteger('lesson_id');
            $table->decimal('time_watched', 10, 2)->nullable();
            $table->tinyInteger('is_completed')->nullable();
            $table->timestamps();

            $table->foreign('purchased_course_id')->references('id')->on('purchased_courses');
            $table->foreign('lesson_id')->references('id')->on('lessons');
        });

        Schema::create('exam', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('productor_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('lesson_id');
            $table->string('title');
            $table->bigInteger('time')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->integer('max_score')->nullable();
            $table->double('min_passing_score')->nullable();
            $table->timestamps();

            $table->foreign('productor_id')->references('id')->on('users');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->foreign('module_id')->references('id')->on('modules');
            $table->foreign('lesson_id')->references('id')->on('lessons');
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->smallInteger('level')->nullable();
            $table->integer('condition_val')->nullable();
            $table->integer('credits')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('badge_id');
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('badge_id')->references('id')->on('badges');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('exam');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('purchased_courses');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('courses');
    }
};
