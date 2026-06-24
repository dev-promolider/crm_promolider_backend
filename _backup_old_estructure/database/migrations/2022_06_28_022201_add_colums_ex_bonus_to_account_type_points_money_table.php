<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumsExBonusToAccountTypePointsMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_type_points_money', function (Blueprint $table) {
            $table->unsignedDouble('pay_4_school',10,2)->default(0);
            $table->unsignedDouble('pay_5_school',10,2)->default(0);
            $table->unsignedDouble('pay_6_school',10,2)->default(0);
            $table->unsignedDouble('pay_7_school',10,2)->default(0);
            $table->unsignedDouble('pay_4_academy',10,2)->default(0);
            $table->unsignedDouble('pay_5_academy',10,2)->default(0);
            $table->unsignedDouble('pay_6_academy',10,2)->default(0);
            $table->unsignedDouble('pay_7_academy',10,2)->default(0);
            $table->unsignedDouble('pay_4_university',10,2)->default(0);
            $table->unsignedDouble('pay_5_university',10,2)->default(0);
            $table->unsignedDouble('pay_6_university',10,2)->default(0);
            $table->unsignedDouble('pay_7_university',10,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_type_points_money', function (Blueprint $table) {
            $table->dropColumn('pay_4_school');
            $table->dropColumn('pay_5_school');
            $table->dropColumn('pay_6_school');
            $table->dropColumn('pay_7_school');
            $table->dropColumn('pay_4_academy');
            $table->dropColumn('pay_5_academy');
            $table->dropColumn('pay_6_academy');
            $table->dropColumn('pay_7_academy');
            $table->dropColumn('pay_4_university');
            $table->dropColumn('pay_5_university');
            $table->dropColumn('pay_6_university');
            $table->dropColumn('pay_7_university');
        });
    }
}
