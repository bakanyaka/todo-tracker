<?php

use Illuminate\Database\Seeder;

class IssuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\User::find(1);
        factory(App\Models\Issue::class, 10)->create()->each(function ($issue) use ($user) {
            $issue->track($user);
        });
    }

}
