<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateGeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('general_settings')
            ->where('id', 1)
            ->update([
                'site_name' => 'BagiBagi',
                'cur_text' => 'IDR',
                'cur_sym' => 'Rp. ',
                'email_from' => 'info@bagibagi.com',
                'email_from_name' => '{{site_name}}',
            ]);
    }
}
