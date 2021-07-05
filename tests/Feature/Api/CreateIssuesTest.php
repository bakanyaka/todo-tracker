<?php

namespace Tests\Feature\Api;

use App\Jobs\SyncIssues;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\IssuesTestCase;

class CreateIssuesTest extends IssuesTestCase
{

    /** @test */
    public function user_can_add_new_issue_to_his_tracked_issues_by_id()
    {

        $issue = $this->makeIssueAndTrackIt(['closed_on' => null]);

        $this->assertDatabaseHas('issues', ['id' => $issue['id']]);

        $response = $this->get(route('api.issues'));

        $response->assertJsonFragment([
            'id' => $issue['id']
        ]);
    }

    /** @test */
    public function user_can_remove_issue_from_his_tracked_issues()
    {
        //Given we have an issue tracked by user
        $user = create('App\Models\User');
        $this->signIn($user);
        $issue = create('App\Models\Issue');
        $issue->track($user);
        $this->assertEquals($issue->id, $user->issues()->first()->id);

        //When we send a request to untrack it
        $response = $this->delete(route('api.issues.untrack', ['issue' => $issue['id']]));
        $response->assertStatus(200);

        //Then it should be removed from his tracked issues
        $this->assertEquals(null, $user->issues()->first());
    }

    /** @test */
    public function issue_id_is_required_to_add_new_issue()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $response = $this->json('POST', route('api.issues.track'), ['issue_id' => '']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['issue_id']);
    }

    /** @test */
    public function user_can_force_an_update_of_all_tracked_issues()
    {
        Bus::fake();
        $this->signIn();

        $this->get(route('api.issues.sync'));

        Bus::assertDispatched(SyncIssues::class);

    }

}
