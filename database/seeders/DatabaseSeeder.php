<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminUserSeeder::class);
        $this->call(PrioritiesSeeder::class);
        $this->call(StatusesSeeder::class);
        //$this->call(IssuesTableSeeder::class);
    }
}
