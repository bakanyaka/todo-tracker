<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::Table('statuses')->insert([
            'id' => 1,
            'name' => 'Новый',
            'is_closed' => false,
            'is_paused' => false,
        ]);
        DB::Table('statuses')->insert([
            'id' => 2,
            'name' => 'Назначен',
            'is_closed' => false,
            'is_paused' => false,
        ]);
        DB::Table('statuses')->insert([
            'id' => 3,
            'name' => 'Заблокирован',
            'is_closed' => false,
            'is_paused' => true,
        ]);
        DB::Table('statuses')->insert([
            'id' => 4,
            'name' => 'Обратная связь',
            'is_closed' => false,
            'is_paused' => true,
        ]);
        DB::Table('statuses')->insert([
            'id' => 5,
            'name' => 'Закрыт',
            'is_closed' => true,
            'is_paused' => false,
        ]);
        DB::Table('statuses')->insert([
            'id' => 6,
            'name' => 'Отказ',
            'is_closed' => true,
            'is_paused' => false,
        ]);
        DB::Table('statuses')->insert([
            'id' => 7,
            'name' => 'В оплату',
            'is_closed' => true,
            'is_paused' => false,
        ]);
        DB::Table('statuses')->insert([
            'id' => 8,
            'name' => 'Закрыто на ПЛ',
            'is_closed' => true,
            'is_paused' => false,
        ]);
        DB::Table('statuses')->insert([
            'id' => 9,
            'name' => 'Выполнено (требует дополнения)',
            'is_closed' => false,
            'is_paused' => true,
        ]);
    }
}
