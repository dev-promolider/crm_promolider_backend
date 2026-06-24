<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEditTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('edit_template', function (Blueprint $table) {
            $table->id();

            $table->foreignId('template_id')->constrained('template')->onDelete('cascade');
            $table->string('section_name');
            $table->json('content')->nullable();
            $table->enum('status', ['published', 'draft', 'edited','Active'])->default('draft');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('edit_template');
    }
}
