<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'admin',
            'username' => 'super-admin',
            'email' => 'bds@mzarsenal.com',
            'password' => bcrypt(env('SUPER_ADMIN_PASSWORD', 'password123')),
            'is_admin' => true,
        ]);
    }
}
