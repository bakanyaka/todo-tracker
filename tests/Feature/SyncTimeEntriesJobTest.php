<?php

namespace Tests\Feature;

use App\Facades\Redmine;
use App\Jobs\SyncTimeEntries;
use App\Models\Synchronization;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncTimeEntriesJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_new_time_entries_if_they_do_not_exist_in_db()
    {
        $timeEntriesRM = $this->makeFakeRedmineTimeEntries([],3);
        Redmine::shouldReceive('getTimeEntries')->once()->andReturn($timeEntriesRM);

        $syncTimeEntriesJob = new SyncTimeEntries();
        $syncTimeEntriesJob->handle();

        $timeEntriesDB = TimeEntry::all();
        $this->assertCount(3, $timeEntriesDB);
        $this->assertDatabaseHas('time_entries',[
            'id' => $timeEntriesRM[0]['id'],
            "assignee_id" => $timeEntriesRM[0]['assignee_id'],
            "project_id" => $timeEntriesRM[0]['project_id'],
            "issue_id" => $timeEntriesRM[0]['issue_id'],
            "hours" => $timeEntriesRM[0]['hours'],
            "comments" => $timeEntriesRM[0]['comments'],
            "spent_on" => $timeEntriesRM[0]['spent_on'],
        ]);

    }

    /** @test */
    public function it_updates_time_entries_if_they_already_exist_in_db()
    {
        $timeEntriesRM = $this->makeFakeRedmineTimeEntries([],3);
        Redmine::shouldReceive('getTimeEntries')->once()->andReturn($timeEntriesRM);

        $syncTimeEntriesJob = new SyncTimeEntries();
        $syncTimeEntriesJob->handle();

        $timeEntriesDB = TimeEntry::all();
        $this->assertCount(3, $timeEntriesDB);
        $this->assertDatabaseHas('time_entries',[
            'id' => $timeEntriesRM[0]['id'],
            "assignee_id" => $timeEntriesRM[0]['assignee_id'],
            "project_id" => $timeEntriesRM[0]['project_id'],
            "issue_id" => $timeEntriesRM[0]['issue_id'],
            "hours" => $timeEntriesRM[0]['hours'],
            "comments" => $timeEntriesRM[0]['comments'],
            "spent_on" => $timeEntriesRM[0]['spent_on'],
        ]);

    }

    /** @test */
    public function it_saves_sync_start_and_complete_timestamps_to_db()
    {
        $now = Carbon::create(2017,12,9,5);
        Carbon::setTestNow($now);
        Redmine::shouldReceive('getTimeEntries')->once()->andReturn(collect());

        $syncJob = new SyncTimeEntries();
        $syncJob->handle();
        $sync = Synchronization::where('type', 'time_entries')->first();

        $this->assertNotNull($sync);
        $this->assertEquals($now,$sync->completed_at);
    }

    private function makeFakeRedmineTimeEntries($attributes = [], $count = 1)
    {
        $issues = [];
        for ($i = 0; $i < $count; $i++) {
            $issues[] = array_merge([
                "id" => $this->faker->unique()->randomNumber(2),
                "assignee_id" => $this->faker->randomNumber(2),
                "project_id" => $this->faker->randomNumber(2),
                "issue_id" => $this->faker->randomNumber(5),
                "hours" => $this->faker->randomNumber(1),
                "comments" => $this->faker->sentence,
                "spent_on" => Carbon::parse($this->faker->date),
            ],$attributes);
        }
        return collect($issues);
    }

}
