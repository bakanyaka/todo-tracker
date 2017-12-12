<?php

namespace Tests\Feature;

use App\Facades\Redmine;

class CreateIssuesTest extends IssuesTestCase
{

    /** @test */
    public function user_can_add_new_issue_to_his_tracked_issues_by_id()
    {

        $issue = $this->makeIssueAndTrackIt();

        $this->assertDatabaseHas('issues', ['id' => $issue['id']]);
        $response = $this->get(route('issues'));
        $response->assertSee((string)$issue['id']);
        $response->assertSee((string)$issue['subject']);
    }

    /** @test */
    public function user_can_remove_issue_from_his_tracked_issues()
    {
        //Given we have an issue tracked by user
        $user = create('App\User');
        $this->signIn($user);
        $issue = create('App\Models\Issue');
        $issue->track($user);
        $this->assertEquals($issue->id, $user->issues()->first()->id);

        //When we send a request to untrack it
        $response = $this->delete(route('issues.untrack', ['id' => $issue['id']]));
        $response->assertRedirect(route('issues'));

        //It should be removed from his tracked issues
        $this->assertEquals(null, $user->issues()->first());
    }

    /** @test */
    public function issue_id_is_required_to_add_new_issue()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $response = $this->json('POST', route('issues.track'), ['issue_id' => '']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['issue_id']);
    }

    /** @test */
    public function user_can_force_an_update_of_all_tracked_issues()
    {
        $issue = create('App\Models\Issue');
        $newIssueData = $this->makeFakeIssueArray();

        Redmine::shouldReceive('getIssue')
            ->once()
            ->with($issue['id'])
            ->andReturn($newIssueData);

        $this->signIn();
        $this->get(route('issues.update'));
        $issue->refresh();

        $this->assertEquals($newIssueData['subject'], $issue->subject);
        $this->assertEquals($newIssueData['closed_on'], $issue->closed_on);
    }

}
