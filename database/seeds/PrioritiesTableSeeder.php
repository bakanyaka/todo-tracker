<?php

use Illuminate\Database\Seeder;

class PrioritiesTableSeeder extends Seeder
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
            'name' => 'Низкий'
        ]);
        DB::table('priorities')->insert([
            'id' => 4,
            'name' => 'Нормальный'
        ]);
        DB::table('priorities')->insert([
            'id' => 5,
            'name' => 'Высокий'
        ]);
        DB::table('priorities')->insert([
            'id' => 6,
            'name' => 'Срочный'
        ]);
        DB::table('priorities')->insert([
            'id' => 7,
            'name' => 'Немедленный'
        ]);
    }
}
