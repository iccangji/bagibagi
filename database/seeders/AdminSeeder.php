<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a new page
        $password = env('ADMIN_PASSWORD');
        DB::table('admins')
            ->where('id', 1)
            ->update([
                'password' => bcrypt($password),
            ]);
    }
}
