<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $paymentMethods = array('Tarjeta crédito / débito', 'Efectivo', 'Binance', 'Paypal', 'Billetera');

        foreach ($paymentMethods as $paymentMethod) {
            DB::table('payment_method')->insert([
                'name' => $paymentMethod,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
