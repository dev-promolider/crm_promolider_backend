<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToolTestimonialsAndFaqsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tool_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('tool_type'); // 'masterclass', 'ebook', 'minicourse'
            $table->unsignedBigInteger('tool_id');
            $table->string('author_name', 150);
            $table->text('content');
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->index(['tool_type', 'tool_id']);
        });

        Schema::create('tool_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('tool_type'); // 'masterclass', 'ebook', 'minicourse'
            $table->unsignedBigInteger('tool_id');
            $table->string('question', 500);
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->index(['tool_type', 'tool_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tool_testimonials');
        Schema::dropIfExists('tool_faqs');
    }
}
