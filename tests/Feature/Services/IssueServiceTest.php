<?php

namespace Services;

use App\Facades\RedmineApi;
use App\Models\Issue;
use App\Models\Service;
use App\Services\IssueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");
    }

    /** @test */
    public function sync_issue_with_redmine()
    {
        $service = factory(Service::class)->create();
        $issueData = $this->makeFakeIssueArray(['service_id' => $service->id]);
        $issue = new Issue(['id' => $issueData['id']]);

        RedmineApi::shouldReceive('getIssue')
            ->once()
            ->with($issueData['id'])
            ->andReturn($issueData);

        (new IssueService())->syncIssueWithRedmine($issue);

        $issue->refresh();
        $this->assertEquals($issueData['subject'], $issue->subject);
        $this->assertEquals($issueData['tracker_id'], $issue->tracker_id);
        $this->assertEquals($issueData['priority_id'], $issue->priority_id);
        $this->assertEquals($issueData['status_id'], $issue->status_id);
        $this->assertEquals($issueData['assigned_to'], $issue->assigned_to);
        $this->assertEquals($issueData['assigned_to_id'], $issue->assigned_to_id);
        $this->assertEquals($issueData['done_ratio'], $issue->done_ratio);
        $this->assertEquals($issueData['created_on'], $issue->created_on);
        $this->assertEquals($issueData['closed_on'], $issue->closed_on);
        $this->assertEquals($issueData['start_date'], $issue->start_date);
        $this->assertEquals($issueData['due_date'], $issue->due_date);
        $this->assertEquals(true, $issue->control);
        $this->assertEquals($service->id, $issue->service_id);
    }

    /** @test */
    public function fill_issue_from_redmine_data()
    {
        $service = factory(Service::class)->create();
        $issueData = $this->makeFakeIssueArray(['service_id' => $service->id, 'parent_id' => 123]);
        $issue = new Issue(['id' => $issueData['id']]);

        (new IssueService())->fillIssueFromRedmineData($issue, $issueData);

        $this->assertEquals($issueData['parent_id'], $issue->parent_id);
        $this->assertEquals($issueData['subject'], $issue->subject);
        $this->assertEquals($issueData['priority_id'], $issue->priority_id);
        $this->assertEquals($issueData['status_id'], $issue->status_id);
        $this->assertEquals($issueData['assigned_to'], $issue->assigned_to);
        $this->assertEquals($issueData['assigned_to_id'], $issue->assigned_to_id);
        $this->assertEquals($issueData['done_ratio'], $issue->done_ratio);
        $this->assertEquals($issueData['created_on'], $issue->created_on);
        $this->assertEquals($issueData['closed_on'], $issue->closed_on);
        $this->assertEquals($issueData['start_date'], $issue->start_date);
        $this->assertEquals($issueData['due_date'], $issue->due_date);
        $this->assertEquals(true, $issue->control);
        $this->assertEquals($service->id, $issue->service_id);
    }

}
