<?php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $c1 = Course::factory(['user_id'=>2])->create();
        $c2 = Course::factory(['user_id'=>2])->create();
        $c3 = Course::factory(['user_id'=>2])->create();
        $c4 = Course::factory(['user_id'=>2])->create();
        $payment1 = Payment::factory(['id_user_sponsor'=>1])->create();
        $payment2 = Payment::factory(['id_user_sponsor'=>1])->create();
        $payment1->courses()->attach($c1,['desc'=>50, 'price' => 50]);
        $payment1->courses()->attach($c2,['desc'=>40, 'price' => 60]);
        $payment2->courses()->attach($c3,['desc'=>50, 'price' => 50]);
        $payment2->courses()->attach($c4,['desc'=>40, 'price' => 60]);
    }
}

