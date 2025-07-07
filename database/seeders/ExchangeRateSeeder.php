<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            ['currency' => 'IDR', 'rates' => 16000],
            ['currency' => 'EUR', 'rates' => 0.92],
            ['currency' => 'GBP', 'rates' => 0.78],
            ['currency' => 'USD' , 'rates' => 1],
            ['currency' => 'JYP' , 'rates' => 145]
        ];

        DB::table('exchange_rates')->insert($rates);
}
}
