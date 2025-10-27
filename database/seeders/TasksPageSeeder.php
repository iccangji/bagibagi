<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasksPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a new page
        DB::table('pages')
            ->insert([
                'name' => 'Tasks',
                'slug' => 'tasks',
                'tempname' => 'templates.basic.',
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
