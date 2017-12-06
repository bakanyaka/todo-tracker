<?php

use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
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
        DB::Table('services')->insert([
            'name' => 'Изменение конфигурации оборудования или программного обеспечения пользователей',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Демонтаж рабочих мест пользователей',
            'hours' => '8'
        ]);
        DB::Table('services')->insert([
            'name' => 'Аварийная замена существующих рабочих мест пользователей',
            'hours' => '8'
        ]);
        DB::Table('services')->insert([
            'name' => 'Ремонт ПК на месте',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Перемещение рабочих мест пользователей',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Установка оргтехники на рабочие места пользователей',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Демонтаж оргтехники с рабочих мест пользователей',
            'hours' => '8'
        ]);
        DB::Table('services')->insert([
            'name' => 'Аварийная замена оргтехники на рабочих местах пользователей',
            'hours' => '6'
        ]);
        DB::Table('services')->insert([
            'name' => 'Перемещение оргтехники',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Ремонт и обслуживание оргтехники',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Настройка оргтехники на местах пользователя',
            'hours' => '4'
        ]);
        DB::Table('services')->insert([
            'name' => 'Замена расходных материалов для оргтехники',
            'hours' => '2'
        ]);
    }
}
