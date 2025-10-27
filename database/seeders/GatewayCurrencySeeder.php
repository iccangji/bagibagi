<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GatewayCurrencySeeder extends Seeder
{
    public function run()
    {
        DB::table('gateway_currencies')->insert([
            [
                'id' => 1,
                'name' => 'Manual',
                'currency' => 'IDR',
                'symbol' => '',
                'method_code' => 1000,
                'gateway_alias' => 'manual',
                'min_amount' => 1.00,
                'max_amount' => 200.00,
                'percent_charge' => 0.00,
                'fixed_charge' => 0.00,
                'rate' => 50000.00,
                'gateway_parameter' => null,
                'created_at' => '2025-10-27 15:03:24',
                'updated_at' => '2025-10-27 15:03:24'
            ],
            [
                'id' => 2,
                'name' => 'Midtrans',
                'currency' => 'IDR',
                'symbol' => '',
                'method_code' => 200,
                'gateway_alias' => 'midtrans',
                'min_amount' => 1.00,
                'max_amount' => 200.00,
                'percent_charge' => 0.00,
                'fixed_charge' => 0.00,
                'rate' => 50000.00,
                'gateway_parameters' => '{"server_key":"' . env('MIDTRANS_SERVER_KEY', '') . '","client_key":"' . env('MIDTRANS_CLIENT_KEY', '') . '"}',
                'created_at' => '2025-10-27 15:05:28',
                'updated_at' => '2025-10-27 15:05:28'
            ]
        ]);
    }
}
