<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use App\Models\Synchronization;
use App\User;
use Carbon\Carbon;
use Tests\Feature\IssuesTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetIssuesTest extends IssuesTestCase
{
    use RefreshDatabase;

    /** @test */
    public function all_necessary_issue_properties_are_returned()
    {
        $user = create(User::class);
        $issue = $this->createTrackedIssue($user,[],false);

        $response = $this->signIn($user)->get(route('api.issues',['status' => 'all']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $issue->id,
            'subject' => $issue->subject,
            'department' => $issue->department,
            'assigned_to' => $issue->assigned_to,
            'priority' => $issue->priority->name,
            'created_on' => (string)$issue->created_on,
            'closed_on' => (string)$issue->closed_on,
            'service' => $issue->service->name,
            'estimated_hours' => $issue->service->hours
        ]);
    }

    /** @test */
    public function timestamp_of_last_sync_with_redmine_is_returned_with_issues()
    {
        $this->signIn();
        $sync = Synchronization::create(['completed_at' => Carbon::now()]);

        $response = $this->get(route('api.issues'));

        $response->assertJsonFragment([
           'last_sync' => [
               'completed_at_human' => $sync->completed_at->diffForHumans(),
               'completed_at' => $sync->completed_at->toDateTimeString(),
           ]
        ]);

    }

    /** @test */
    public function user_gets_only_his_own_tracked_open_issues_by_default()
    {
        //Given we have an open issue tracked by user
        $user = create('App\User');
        $openIssue = $this->createTrackedIssue($user,[]);
        //And closed issue tracked by user
        $closedIssue = $this->createTrackedIssue($user,[],false);
        
        //And issue tracked by another user
        $otherIssue = $this->createTrackedIssue(create('App\User'));

        //When user makes request to get his issues,
        $response = $this->signIn($user)->get(route('api.issues'));

        //Response contains only his own tracked issues
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $openIssue->id,
        ]);
        $response->assertJsonMissing([
            'id' => $otherIssue->id
        ]);
        $response->assertJsonMissing([
            'id' => $closedIssue->id
        ]);
    }

    /** @test */
    public function user_can_get_own_tracked_closed_issues()
    {
        //Given we have an closed issue tracked by user
        $user = create('App\User');
        $closedIssue = $this->createTrackedIssue($user,[],false);

        //When user makes request to get his closed issues,
        $response = $this->signIn($user)->get(route('api.issues', ['status' => 'closed']));

        //Response contains only his tracked closed issue
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $closedIssue->id,
        ]);

    }


    /** @test */
    public function user_can_get_all_issues_tracked_by_users()
    {
        //Given we have a user
        $this->signIn();
        //And issue tracked by another user
        $issue = $this->createTrackedIssue();
        //Amd not tracked issued
        $notTrackedIssue = create(Issue::class);

        //When user makes request to get all issues tracked bt users,
        $response = $this->get(route('api.issues', ['user' => 'all']));

        //Response contains issue tracked by another user
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $issue->id,
        ]);
        //Amd does not contain not tracked issue
        $response->assertJsonMissing([
           'id' => $notTrackedIssue->id
        ]);
    }

    /** @test */
    public function user_can_get_open_issues_marked_for_control()
    {
        //Given we have a user
        $this->signIn();
        //And an issue marked for control not tracked by anyone
        $issue = create(Issue::class,['control' => true]);

        //When user makes request to get all issues marked for control,
        $response = $this->get(route('api.issues', ['user' => 'control']));

        //Response contains issue marked for control
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $issue->id,
        ]);
    }

    /** @test */
    public function user_can_get_issues_created_within_given_date_interval()
    {
        //Given we have a user
        $user = create('App\User');
        $this->signIn($user);
        //And two dates
        $dateOne = Carbon::parse('2018-01-17');
        $dateTwo = Carbon::parse('2018-01-19');
        //One tracked issue that was created before given interval
        $beforeIssue = $this->createTrackedIssue($user, ['created_on' => '2018-01-16 10:00:00']);
        //And another tracked issue that was created after interval
        $afterIssue = $this->createTrackedIssue($user, ['created_on' => '2018-01-20 10:00:00']);
        //And another tracked issue that was created within given interval
        $withinIssue = $this->createTrackedIssue($user, ['created_on' => '2018-01-18 10:00:00']);
        //When user makes request to get issues created within given interval
        $response = $this->get(route('api.issues', [
            'user' => 'all',
            'created_after' => $dateOne->toDateString(),
            'created_before' => $dateTwo->toDateString(),
        ]));
        //Then he can see only issue that was created within given interval
        $response->assertJsonMissing([
            'id' => $beforeIssue->id
        ]);
        $response->assertJsonMissing([
            'id' => $afterIssue->id
        ]);
        $response->assertJsonFragment([
            'id' => $withinIssue->id
        ]);
    }



}
