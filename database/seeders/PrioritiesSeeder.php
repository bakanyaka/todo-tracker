<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioritiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('priorities')->insert([
            'id' => 3,
            'name' => 'Низкий',
        ]);
        DB::table('priorities')->insert([
            'id' => 4,
            'name' => 'Нормальный',
        ]);
        DB::table('priorities')->insert([
            'id' => 5,
            'name' => 'Высокий',
        ]);
        DB::table('priorities')->insert([
            'id' => 6,
            'name' => 'Срочный',
        ]);
        DB::table('priorities')->insert([
            'id' => 7,
            'name' => 'Немедленный',
        ]);
    }
}
