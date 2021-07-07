<?php

namespace Tests\Feature;

use App\Facades\RedmineApi;
use App\Jobs\SyncIssues;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Service;
use App\Models\Synchronization;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncIssuesJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");
    }

    /** @test */
    public function it_creates_new_issue_if_it_does_not_exist()
    {
        $service = create(Service::class);
        create(Project::class,['id' => 2]);
        $redmineIssue = $this->makeFakeIssueArray(['service_id' => $service->id, 'project_id' => 2]);
        RedmineApi::shouldReceive('getUpdatedIssues')->once()->andReturn(collect([$redmineIssue]));

        SyncIssues::dispatch();

        $issue = Issue::find($redmineIssue['id']);

        $this->assertNotNull($issue);
        $this->assertEquals($redmineIssue['subject'], $issue->subject);
        $this->assertEquals($redmineIssue['assigned_to'], $issue->assigned_to);
        $this->assertEquals($redmineIssue['assigned_to_id'], $issue->assigned_to_id);
        $this->assertEquals($redmineIssue['service_id'], $issue->service_id);
        $this->assertEquals($redmineIssue['priority_id'], $issue->priority_id);
        $this->assertEquals($redmineIssue['project_id'], $issue->project_id);
        $this->assertEquals($redmineIssue['tracker_id'], $issue->tracker_id);
        $this->assertEquals($redmineIssue['status_id'], $issue->status_id);
        $this->assertEquals($redmineIssue['created_on'], $issue->created_on);
        $this->assertEquals($redmineIssue['closed_on'], $issue->closed_on);
    }

    /** @test */
    public function it_updates_issue_if_it_already_exists()
    {
        $issue = factory(Issue::class)->create();
        $redmineIssue = $this->makeFakeIssueArray([
            'id' => $issue->id,
            'service_id' => factory(Service::class)->create()->id,
            'project_id' => factory(Project::class)->create()->id,
            'status_id' => 4
        ]);
        RedmineApi::shouldReceive('getUpdatedIssues')->once()->andReturn(collect([$redmineIssue]));

        SyncIssues::dispatch();

        $updatedIssue = $issue->fresh();
        $this->assertEquals($redmineIssue['subject'], $updatedIssue->subject);
        $this->assertEquals($redmineIssue['assigned_to'], $updatedIssue->assigned_to);
        $this->assertEquals($redmineIssue['assigned_to_id'], $updatedIssue->assigned_to_id);
        $this->assertEquals($redmineIssue['service_id'], $updatedIssue->service_id);
        $this->assertEquals($redmineIssue['priority_id'], $updatedIssue->priority_id);
        $this->assertEquals($redmineIssue['project_id'], $updatedIssue->project_id);
        $this->assertEquals($redmineIssue['status_id'], $updatedIssue->status_id);
        $this->assertEquals($redmineIssue['created_on'], $updatedIssue->created_on);
        $this->assertEquals($redmineIssue['closed_on'], $updatedIssue->closed_on);
    }

    /** @test */
    public function it_saves_sync_start_and_complete_timestamps_to_db()
    {
        $now = Carbon::create(2017,12,9,5);
        Carbon::setTestNow($now);
        RedmineApi::shouldReceive('getUpdatedIssues')->once()->andReturn(collect());

        SyncIssues::dispatch();

        $sync = Synchronization::where('type', 'issues')->first();
        $this->assertNotNull($sync);
        $this->assertEquals($now,$sync->completed_at);
    }

    /** @test */
    public function it_retrieves_only_redmine_issues_updated_since_last_sync()
    {
        Synchronization::create(['completed_at' => Carbon::parse('2017-12-10'), 'type' => 'issues']);
        Synchronization::create(['completed_at' => Carbon::parse('2017-12-13'), 'type' => 'issues']);
        $completedAt = Carbon::parse('2017-12-15 10:00');
        Synchronization::create(['completed_at' => $completedAt, 'type' => 'issues']);
        RedmineApi::shouldReceive('getUpdatedIssues')->once()->with(\Mockery::on(function (Carbon $dt) use ($completedAt) {
            return $dt == $completedAt;
        }))->andReturn(collect([]));

        SyncIssues::dispatch();
    }

    /** @test */
    public function it_retrieves_redmine_issues_updated_since_date_specified()
    {
        $date = Carbon::parse('2017-12-15 10:00');
        RedmineApi::shouldReceive('getUpdatedIssues')->once()->with(\Mockery::on(function (Carbon $dt) use ($date) {
            return $dt == $date;
        }))->andReturn(collect([]));

        SyncIssues::dispatch($date);
    }

    /** @test */
    public function it_only_updates_issues_that_were_modified()
    {
        Carbon::setTestNow('2018-01-22 15:00:00');
        // Given we have an issue that wasn't modified
        $notModifiedIssue = create(Issue::class, [
            'created_on' => '2018-01-22 12:00:00',
            'subject' => 'old subject',
            'updated_on' => '2018-01-22 12:00:00'
        ]);
        $redmineIssues[] = $this->makeFakeIssueArray([
            'id' => $notModifiedIssue->id,
            'subject' => 'new subject',
            'created_on' => Carbon::parse('2018-01-22 12:00:00'),
            'updated_on' => Carbon::parse('2018-01-22 12:00:00')
        ]);
        // And Issue that was modified
        $modifiedIssue = create(Issue::class, [
            'created_on' => '2018-01-22 12:00:00',
            'subject' => 'old subject',
            'updated_on' => '2018-01-22 12:00:00'
        ]);
        $redmineIssues[] = $this->makeFakeIssueArray([
            'id' => $modifiedIssue->id,
            'subject' => 'new subject',
            'created_on' => Carbon::parse('2018-01-22 12:00:00'),
            'updated_on' => Carbon::parse('2018-01-22 13:00:00'),
        ]);
        // When sync issues job is called
        RedmineApi::shouldReceive('getUpdatedIssues')->once()->andReturn(collect($redmineIssues));
        SyncIssues::dispatch();
        // Then only issue that was modified should be updated in database
        $notModifiedIssue = $notModifiedIssue->fresh();
        $modifiedIssue = $modifiedIssue->fresh();
        $this->assertEquals('new subject', $modifiedIssue->subject);
        $this->assertEquals('old subject', $notModifiedIssue->subject);
    }

}
