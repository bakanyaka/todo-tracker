<?php

namespace Tests\Feature;

use App\BusinessDate;
use App\Models\Issue;
use App\Services\RedmineService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\MakesFakeIssues;

class IssuesTest extends TestCase
{
    use RefreshDatabase;
    use MakesFakeIssues;

    /** @test */
    public function user_can_view_own_tracked_issues()
    {

        //Given we have an issue tracked by user
        $user = create('App\User');
        $issue = create('App\Models\Issue');
        $issue->trackedByUsers()->attach($user);

        //And issue tracked by another user
        $otherUser = create('App\User');
        $otherIssue = create('App\Models\Issue');
        $otherIssue->trackedByUsers()->attach($otherUser);

        //When user visits issues page, he can see his tracked issues
        //and can't see other user's tracked issues
        $response = $this->signIn($user)->get(route('issues'));
        $response->assertStatus(200);
        $response->assertSee((string)$issue->id);
        $response->assertDontSee((string)$otherIssue->id);

    }

    /** @test */
/*    public function create_new_issue_form_is_auto_filled_with_data_retrieved_from_redmine()
    {
        $issue = $this->makeFakeIssue();
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->app->bind('App\Services\RedmineService', function($app) use ($client) {
            return new RedmineService($client);
        });
        $response = $this->signIn()->get(route('issues.create', ['issue_id' => 1]));
        $response->assertSee($issue['issue']['subject']);
    }*/

    /** @test */
    public function user_can_add_new_issue()
    {

        $issue = make('App\Models\Issue');

        $this->signIn();
        $response = $this->post(route('issues.store'), [
            'issue_id' => $issue->id,
            'subject' => $issue->subject,
            'created_on' => '2017-12-04T15:00:00Z',
            'estimated_hours' => 3
        ]);
        $this->assertDatabaseHas('issues', ['id' => $issue->id]);
        $response = $this->get(route('issues'));
        $response->assertSee($issue->subject);
        $response->assertSee('2017-12-05 10:00');
    }

}
