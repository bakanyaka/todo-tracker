<?php

namespace Tests\Feature;

use App\BusinessDate;
use App\Models\Issue;
use App\Models\Priority;
use App\Models\Service;
use Carbon\Carbon;

class ViewIssuesTest extends IssuesTestCase
{

    /** @test */
    public function user_can_view_all_issues_tracked_by_users()
    {
        //Given we have a user
        $this->signIn();

        //And issue tracked by another user
        $otherUser = create('App\User');
        $otherIssue = create('App\Models\Issue');
        $otherIssue->track($otherUser);

        //When we visit issues page with
        $response = $this->get(route('issues', ['user' => 'all']));
        //We can see issue tracked by another user
        $response->assertStatus(200);
        $response->assertSee((string)$otherIssue->subject);
        $response->assertSee((string)$otherIssue->id);
    }

    /** @test */
    public function user_can_not_view_issues_tracked_by_no_one()
    {
        $this->signIn();
        $issue = create('App\Models\Issue');

        $response = $this->get(route('issues', ['user' => 'all']));

        $response->assertStatus(200);
        $response->assertDontSee($issue->subject);
        $response->assertDontSee((string)$issue->id);
    }


    /** @test */
    public function user_can_view_his_own_tracked_issues()
    {

        //Given we have an issue tracked by user
        $user = create('App\User');
        $issue = $this->createTrackedIssue($user);

        //And issue tracked by another user
        $otherUser = create('App\User');
        $otherIssue = $this->createTrackedIssue($otherUser);

        //When user visits issues page, he can see his tracked issues
        //and can't see other user's tracked issues
        $response = $this->signIn($user)->get(route('issues'));
        $response->assertStatus(200);
        $response->assertSee((string)$issue->id);
        $response->assertDontSee((string)$otherIssue->id);

    }

    /** @test */
    public function user_can_filter_tasks_based_on_completion()
    {
        $this->signIn();
        $incompleteIssue = $this->createTrackedIssue();
        $completeIssue = $this->createTrackedIssue(null,['closed_on' => Carbon::now()]);

        $response = $this->get(route('issues', ['user' => 'all', 'only_open' => 'true']));

        $response->assertSee((string)$incompleteIssue->id);
        $response->assertDontSee((string)$completeIssue->id);
    }

    /** @test */
    public function all_necessary_issue_properties_are_loaded_from_redmine_and_displayed()
    {
        Service::create([
            'name' => 'Тестирование',
            'hours' => 333
        ]);
        $priority = Priority::create([
            'name' => 'Вчера'
        ]);
        $created_on = BusinessDate::parse('2017-12-06 09:00:00');
        $closed_on = BusinessDate::instance($created_on)->addHours(100);
        $issue = $this->makeIssueAndTrackIt([
            'service' => 'Тестирование',
            'priority_id' => $priority->id,
            'department' => 'Тестовое подразделение',
            'created_on' => $created_on,
            'closed_on' => $closed_on
        ]);

        $response = $this->get(route('issues',['user'=> 'all','only_open' => 'false']));

        $response->assertStatus(200);
        $response->assertSee((string)$issue['id']);
        $response->assertSee($issue['subject']);
        $response->assertSee($issue['department']);
        $response->assertSee($issue['assigned_to']);
        $response->assertSee((string)$created_on);
        $response->assertSee((string)$closed_on);
        $response->assertSee($issue['service']);
        $response->assertSee('333');
        $response->assertSee('Вчера');
        $response->assertSee((string)$created_on->addBusinessHours(333));

    }

}
