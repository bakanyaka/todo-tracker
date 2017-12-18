<?php

namespace Tests\Feature;

use App\Facades\Redmine;
use App\Models\Issue;
use App\Services\Sync;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        $this->artisan("db:Seed", ['--class' => 'PrioritiesTableSeeder']);
    }

    /** test */
    public function it_syncs_all_redmine_issues_updated_since_last_sync()
    {
        $startedAtDate = Carbon::parse('2017-12-15 10:00');
        $issue = $this->makeFakeIssueArray();
        \App\Models\Synchronization::create(['started_at' => $startedAtDate]);
        $syncService = new Sync();

        $syncService->synchronize();

        Redmine::shouldReceive('getUpdatedIssues')->once()->with($startedAtDate)->andReturn(collect([$issue]));
        $this->assertDatabaseHas();
    }

    /** @test */
    public function it_creates_new_issue_if_it_does_not_exist()
    {
        $service = create('App\Models\Service');
        $redmineIssue = $this->makeFakeIssueArray(['service' => $service->name]);
        $syncService = new Sync();
        Redmine::shouldReceive('getUpdatedIssues')->once()->andReturn(collect([$redmineIssue]));

        $syncService->synchronize();
        $issue = Issue::find($redmineIssue['id']);

        $this->assertNotNull($issue);
        $this->assertEquals($redmineIssue['subject'], $issue->subject);
        $this->assertEquals($redmineIssue['department'], $issue->department);
        $this->assertEquals($redmineIssue['assigned_to'], $issue->assigned_to);
        $this->assertEquals($redmineIssue['service'], $issue->service->name);
        $this->assertEquals($redmineIssue['priority_id'], $issue->priority_id);
        $this->assertEquals($redmineIssue['created_on'], $issue->created_on);
        $this->assertEquals($redmineIssue['closed_on'], $issue->closed_on);
    }

    /** @test */
    public function it_updates_issue_if_it_already_exists()
    {
        $issue = create('App\Models\Issue');
        $service = create('App\Models\Service');
        $redmineIssue = $this->makeFakeIssueArray([
            'id' => $issue->id,
            'service' => $service->name
        ]);
        $syncService = new Sync();
        Redmine::shouldReceive('getUpdatedIssues')->once()->andReturn(collect([$redmineIssue]));

        $syncService->synchronize();
        $updatedIssue = $issue->fresh();

        $this->assertEquals($redmineIssue['subject'], $updatedIssue->subject);
        $this->assertEquals($redmineIssue['department'], $updatedIssue->department);
        $this->assertEquals($redmineIssue['assigned_to'], $updatedIssue->assigned_to);
        $this->assertEquals($redmineIssue['service'], $updatedIssue->service->name);
        $this->assertEquals($redmineIssue['priority_id'], $updatedIssue->priority_id);
        $this->assertEquals($redmineIssue['created_on'], $updatedIssue->created_on);
        $this->assertEquals($redmineIssue['closed_on'], $updatedIssue->closed_on);
    }

    /** @test */
    public function it_saves_sync_start_and_complete_timestamps_to_db()
    {
        $syncService = new Sync();
        Redmine::shouldReceive('getUpdatedIssues')->once()->andReturn(collect());

        $syncService->synchronize();
        $sync = \App\Models\Synchronization::first();

        $this->assertEquals(Carbon::now(),$sync->completed_at);

    }

}
