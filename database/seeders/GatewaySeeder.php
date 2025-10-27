<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GatewaySeeder extends Seeder
{
    public function run()
    {
        DB::table('gateways')->insert([
            [
                'id' => 61,
                'form_id' => 1,
                'code' => 1000,
                'name' => 'Manual',
                'alias' => 'manual',
                'image' => '68ff2a123e91c1761552914.png',
                'status' => 1,
                'gateway_parameters' => '[]',
                'supported_currencies' => '[]',
                'crypto' => 0,
                'extra' => null,
                'description' => '<br>',
                'created_at' => '2025-10-27 15:03:24',
                'updated_at' => '2025-10-27 15:03:24'
            ],
            [
                'id' => 62,
                'form_id' => 2,
                'code' => 200,
                'name' => 'Midtrans',
                'alias' => 'midtrans',
                'image' => '68ff27c8cf3ce1761552328.png',
                'status' => 1,
                'gateway_parameters' => '{"secret_key":{"title":"Server Key","global":true,"value":"' . env('MIDTRANS_SERVER_KEY') . '"},"client_key":{"title":"Client Key","global":true,"value":"' . env('MIDTRANS_CLIENT_KEY') . '"}}',
                'supported_currencies' => '[]',
                'crypto' => 0,
                'extra' => null,
                'description' => '<br>',
                'created_at' => '2025-10-27 15:05:28',
                'updated_at' => '2025-10-27 15:05:28'
            ]
        ]);
    }
}
