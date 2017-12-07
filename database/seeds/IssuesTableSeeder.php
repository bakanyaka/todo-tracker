<?php

use Carbon\Carbon;
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
        factory(App\Models\Issue::class)->create([
            'service_id' => 1,
            'created_on' => Carbon::create(2017,12,07,11),
            'closed_on' => Carbon::create(2017,12,07,14),
        ])->each(function ($issue) use ($user) {
            $issue->track($user);
        });
    }

}
