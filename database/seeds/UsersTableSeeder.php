<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User::class)->create([
            'name' => 'Иванов Иван',
            'email' => 'test@gmail.com',
            'username' => 'test',
            'password' => bcrypt('test')
        ]);
    }
}
