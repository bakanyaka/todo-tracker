<?php

use Illuminate\Database\Seeder;

class ServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::Table('services')->insert([
            'name' => 'Приём и регистрация заявки',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Регистрация пользователя в информационных системах',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Выдача доступа к ресурсам ЛВС',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Организация рабочих мест пользователей',
            'hours' => '24'
        ]);
    }
}
