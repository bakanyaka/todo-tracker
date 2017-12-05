<?php

namespace Tests\Feature;

use App\Facades\Redmine;
use App\Models\Service;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IssuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        $this->artisan("db:Seed");
    }

    /** @test */
    public function user_can_view_own_tracked_issues()
    {

        //Given we have an issue tracked by user
        $user = create('App\User');
        $issue = create('App\Models\Issue');
        $issue->track($user);

        //And issue tracked by another user
        $otherUser = create('App\User');
        $otherIssue = create('App\Models\Issue');
        $otherIssue->track($otherUser);

        //When user visits issues page, he can see his tracked issues
        //and can't see other user's tracked issues
        $response = $this->signIn($user)->get(route('issues'));
        $response->assertStatus(200);
        $response->assertSee((string)$issue->id);
        $response->assertDontSee((string)$otherIssue->id);

    }

    /** @test */
    public function user_can_add_new_issue_by_id()
    {

        $issue = $this->makeFakeIssueArray();
        $issueId = $issue['id'];

        Redmine::shouldReceive('getIssue')
            ->once()
            ->with($issueId)
            ->andReturn($issue);

        $this->signIn();
        $this->post(route('issues.track'), ['issue_id' => $issueId]);

        $this->assertDatabaseHas('issues', ['id' => $issueId]);
        $response = $this->get(route('issues'));
        $response->assertSee((string)$issueId);
        $response->assertSee((string)$issue['subject']);
    }
}
