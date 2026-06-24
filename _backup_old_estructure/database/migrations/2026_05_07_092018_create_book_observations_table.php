<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analyst_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->text('observations');
            $table->enum('status', ['approved', 'disapproved'])->default('disapproved');
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
        Schema::dropIfExists('book_observations');
    }
}
