<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('binary_tree', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('binary_sponsor');
            $table->string('position'); // L/R o 0/1
            $table->integer('classification')->default(1);
            $table->string('status')->default('0');
            $table->string('authorized')->default('1');
            $table->unsignedBigInteger('user_position_left')->nullable();
            $table->unsignedBigInteger('user_position_right')->nullable();
            $table->unsignedBigInteger('user_above')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('binary_sponsor')->references('id')->on('users');
            $table->foreign('user_position_left')->references('id')->on('users');
            $table->foreign('user_position_right')->references('id')->on('users');
            $table->foreign('user_above')->references('id')->on('users');
        });

        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sponsor_id');
            $table->decimal('points_val', 10, 2);
            $table->integer('side'); // 0 = izq, 1 = der
            $table->integer('status')->default(1);
            $table->string('reason');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('sponsor_id')->references('id')->on('users');
        });

        Schema::create('rank_bonus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('vol_min', 10, 2)->default(0);
            $table->integer('active_direct')->default(0);
            $table->decimal('max_pay', 10, 2)->default(0);
            $table->decimal('monthly_bonus', 10, 2)->default(0);
            $table->decimal('extra_bonus', 10, 2)->default(0);
            $table->integer('limit_generation')->default(0);
            $table->integer('pack_max')->default(0);
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('binary_cut_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rank_id');
            $table->decimal('left_points', 15, 2);
            $table->decimal('right_points', 15, 2);
            $table->decimal('transferred_amount', 15, 2);
            $table->integer('batch');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('rank_id')->references('id')->on('rank_bonus');
        });

        Schema::create('generational_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rank_id');
            $table->decimal('g_1', 5, 2)->default(0);
            $table->decimal('g_2', 5, 2)->default(0);
            $table->decimal('g_3', 5, 2)->default(0);
            $table->decimal('g_4', 5, 2)->default(0);
            $table->decimal('g_5', 5, 2)->default(0);
            $table->decimal('g_6', 5, 2)->default(0);
            $table->decimal('g_7', 5, 2)->default(0);
            $table->decimal('g_8', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('rank_id')->references('id')->on('rank_bonus');
        });

        Schema::create('expansion_bonus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_type_id');
            $table->string('name');
            $table->integer('value');
            $table->timestamps();

            $table->foreign('account_type_id')->references('id')->on('account_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expansion_bonus');
        Schema::dropIfExists('generational_bonuses');
        Schema::dropIfExists('binary_cut_histories');
        Schema::dropIfExists('rank_bonus');
        Schema::dropIfExists('points');
        Schema::dropIfExists('binary_tree');
    }
};
