<?php

namespace Tests\Feature;

use App\BusinessDate;
use App\Facades\Redmine;
use App\Models\Priority;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        $this->artisan("db:Seed", ['--class' => 'PrioritiesTableSeeder']);
    }


    /** @test */
    public function user_can_view_his_own_tracked_issues()
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

        $response = $this->get(route('issues'));

        $response->assertStatus(200);
        $response->assertSee((string)$issue['id']);
        $response->assertSee($issue['subject']);
        $response->assertSee($issue['department']);
        $response->assertSee((string)$created_on);
        $response->assertSee((string)$closed_on);
        $response->assertSee($issue['service']);
        $response->assertSee('333');
        $response->assertSee('Вчера');
        $response->assertSee((string)$created_on->addBusinessHours(333));

    }

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
        $this->assertEquals($issue->id,$user->issues()->first()->id);

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

        $this->assertEquals($newIssueData['subject'],$issue->subject);
        $this->assertEquals($newIssueData['closed_on'],$issue->closed_on);
    }

    /**
     * @param array $attributes
     * @return array
     */
    private function makeIssueAndTrackIt($attributes=[])
    {
        $issue = $this->makeFakeIssueArray($attributes);
        $issueId = $issue['id'];

        Redmine::shouldReceive('getIssue')
            ->once()
            ->with($issueId)
            ->andReturn($issue);

        $this->signIn();
        $this->post(route('issues.track'), ['issue_id' => $issueId]);
        return $issue;
    }

}
