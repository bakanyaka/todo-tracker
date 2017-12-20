<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use App\User;
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

        $response = $this->signIn($user)->get(route('api.issues',['only_open' => 'false']));

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
/*
        $response->assertSee((string)$created_on->addBusinessHours(333));*/
    }

    /** @test */
    public function user_can_get_his_tracked_issues()
    {
        //Given we have an issue tracked by user
        $user = create('App\User');
        $issue = $this->createTrackedIssue($user);
        //And issue tracked by another user
        $otherIssue = $this->createTrackedIssue(create('App\User'));

        //When user makes request to get hus issues,
        $response = $this->signIn($user)->get(route('api.issues'));

        //Response contains only his own tracked issues
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $issue->id,
        ]);
        $response->assertJsonMissing([
            'id' => $otherIssue->id
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

}
