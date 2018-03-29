<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Service;
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
        $issue = $issue->fresh();

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
            'estimated_hours' => $issue->service->hours,
            'is_paused' => $issue->status->is_paused,
            'is_closed' => $issue->status->is_closed
        ]);
    }

    /** @test */
    public function timestamp_of_last_sync_with_redmine_is_returned_with_issues()
    {
        $this->signIn();
        $sync = Synchronization::create(['completed_at' => Carbon::now(), 'type' => 'issues']);

        $response = $this->get(route('api.issues'));

        $response->assertJsonFragment([
           'last_sync' => [
               'completed_at_human' => $sync->completed_at->diffForHumans(),
               'completed_at' => $sync->completed_at->toDateTimeString(),
           ]
        ]);
    }

    /** @test */
    public function user_can_gets_his_own_tracked_open_issues()
    {
        //Given we have an open issue tracked by user
        $user = create('App\User');
        $openIssue = $this->createTrackedIssue($user,[]);
        //And closed issue tracked by user
        $closedIssue = $this->createTrackedIssue($user,[],false);
        
        //And issue tracked by another user
        $otherIssue = $this->createTrackedIssue(create('App\User'));

        //When user makes request to get his issues,
        $response = $this->signIn($user)->get(route('api.issues', ['user' => 'me']));

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
    public function user_can_get_all_paused_issues()
    {
        // Given we have paused issue
        $pausedIssue = create(Issue::class, ['status_id' => 4]);
        // And not paused issue
        $notPausedIssue = create(Issue::class);
        // When user makes request to get paused issues
        $response = $this->signIn()->get(route('api.issues', ['status' => 'paused']));
        // Then response only contains paused issue
        $response->assertJsonMissing([
            'id' => $notPausedIssue->id
        ]);
        $response->assertJsonFragment([
            'id' => $pausedIssue->id
        ]);
    }

    /** @test */
    public function user_can_get_all_overdue_issues()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');
        // Given we have overdue issue
        $service = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $overdueIssue = factory(Issue::class)->create([
            'created_on' => Carbon::parse('2018-01-19 09:00:00'),
            'service_id' => $service->id
        ]);
        // And not overdue issue
        $notOverdueIssue = create(Issue::class);

        // When user makes request to get overdue issues
        $this->signIn();
        $response = $this->get(route('api.issues', ['overdue' => 'yes']));
        // Then response only contains overdue issue
        $response->assertJsonMissing([
            'id' => $notOverdueIssue->id
        ]);
        $response->assertJsonFragment([
            'id' => $overdueIssue->id
        ]);
    }

    /** @test */
    public function user_can_get_all_issues_with_associated_service_that_are_not_overdue()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');
        // Given we have overdue issue
        $service = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $overdueIssue = factory(Issue::class)->create([
            'created_on' => Carbon::parse('2018-01-19 09:00:00'),
            'service_id' => $service->id
        ]);
        // And not overdue issue
        $notOverdueIssue = create(Issue::class, [
            'created_on' => '2018-01-19 14:00:00',
            'service_id' => $service->id
        ]);

        // When user makes request to get non overdue issues
        $this->signIn();
        $response = $this->get(route('api.issues', ['overdue' => 'no']));
        // Then response only contains overdue issue
        $response->assertJsonMissing([
            'id' => $overdueIssue->id
        ]);
        $response->assertJsonFragment([
            'id' => $notOverdueIssue->id
        ]);
    }

    /** @test */
    public function user_can_get_all_open_issues_created_within_specified_period()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');

        // Given we have open issues created within specified period
        $issuesShouldBeIncluded[] = create(Issue::class, [
            'created_on' => '2018-01-12 00:00:00'
        ]);
        $issuesShouldBeIncluded[] = create(Issue::class, [
            'created_on' => '2018-01-18 23:59:00'
        ]);

        // And issues created before and after specified period
        $issuesShouldBeMissing[] = create(Issue::class, [
            'created_on' => '2018-01-11 12:00:00'
        ]);
        $issuesShouldBeMissing[] = create(Issue::class, [
            'created_on' => '2018-01-19 12:00:00'
        ]);

        // And closed issues created within specified period
        $issuesShouldBeMissing[] = factory(Issue::class)->states(['closed'])->create([
            'created_on' => '2018-01-15 23:59:00'
        ]);

        // When user makes request to get all open issues within period
        $this->signIn();
        $response = $this->get(route('api.issues', [
            'period_from_date' => '2018-01-12',
            'period_to_date' => '2018-01-18'
        ]));
        // Then response only contains issue created within period
        $response->assertJsonFragment([
            'id' => $issuesShouldBeIncluded[0]->id
        ]);
        $response->assertJsonFragment([
            'id' => $issuesShouldBeIncluded[1]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[0]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[1]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[2]->id
        ]);
    }

    /** @test */
    public function user_can_get_all_issues_created_within_specified_period()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');

        // Given we have both open and closed issues created within specified period
        $issuesShouldBeIncluded[] = create(Issue::class, [
            'created_on' => '2018-01-12 00:00:00'
        ]);
        $issuesShouldBeIncluded[] = factory(Issue::class)->states(['closed'])->create([
            'created_on' => '2018-01-18 23:59:00'
        ]);

        // And issues created before and after specified period
        $issuesShouldBeMissing[] = create(Issue::class, [
            'created_on' => '2018-01-11 12:00:00'
        ]);
        $issuesShouldBeMissing[] = create(Issue::class, [
            'created_on' => '2018-01-19 12:00:00'
        ]);

        // When user makes request to get all open issues within period
        $this->signIn();
        $response = $this->get(route('api.issues', [
            'status' => 'all',
            'period_from_date' => '2018-01-12',
            'period_to_date' => '2018-01-18'
        ]));
        // Then response only contains issue created within period
        $response->assertJsonFragment([
            'id' => $issuesShouldBeIncluded[0]->id
        ]);
        $response->assertJsonFragment([
            'id' => $issuesShouldBeIncluded[1]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[0]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[1]->id
        ]);
    }

    /** @test */
    public function user_can_get_all_issues_closed_in_a_specified_period()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');

        // Given we have issues closed closed within specified period
        $issuesShouldBeIncluded[] = factory(Issue::class)->states(['closed'])->create([
            'closed_on' => '2018-01-12 00:00:00'
        ]);
        $issuesShouldBeIncluded[] = factory(Issue::class)->states(['closed'])->create([
            'closed_on' => '2018-01-18 23:59:00'
        ]);

        // And issues closed before and after specified period
        $issuesShouldBeMissing[] = factory(Issue::class)->states(['closed'])->create([
            'closed_on' => '2018-01-11 23:59:00'
        ]);
        $issuesShouldBeMissing[] = factory(Issue::class)->states(['closed'])->create([
            'closed_on' => '2018-01-19 0:00:00'
        ]);
        // And open issue
        $issuesShouldBeMissing[] = create(Issue::class, [
            'created_on' => '2018-01-13 12:00:00'
        ]);

        // When user makes request to get all closed issues within period
        $this->signIn();
        $response = $this->get(route('api.issues', [
            'status' => 'closed',
            'period_from_date' => '2018-01-12',
            'period_to_date' => '2018-01-18'
        ]));
        // Then response only contains issue created within period
        $response->assertJsonFragment([
            'id' => $issuesShouldBeIncluded[0]->id
        ]);
        $response->assertJsonFragment([
            'id' => $issuesShouldBeIncluded[1]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[0]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[1]->id
        ]);
        $response->assertJsonMissing([
            'id' => $issuesShouldBeMissing[2]->id
        ]);
    }

    /** @test */
    public function user_can_get_due_today_issues_that_have_less_than_30_percent_of_time_left()
    {
        Carbon::setTestNow('2018-01-19 10:00:00');

        $twoHoursService = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $twentyFourHoursService = create(Service::class, [
            'name' => 'Разработка',
            'hours' => 24
        ]);
        //Due Today issue but have more than 30 percent of time left
        $dueTodayIssueMoreTimeLeft = factory(Issue::class)->create([
            'created_on' => Carbon::now(),
            'service_id' => $twoHoursService->id
        ]);
        //Due Today issues and have less 30 percent of time left
        $dueTodayIssueLessTimeLeft = factory(Issue::class)->create([
            'created_on' => '2018-01-19 08:10:00',
            'service_id' => $twoHoursService->id
        ]);
        //Not Due Today Issues
        $notDueTodayIssue = factory(Issue::class)->create([
            'created_on' => Carbon::now(),
            'service_id' => $twentyFourHoursService->id
        ]);

        $this->signIn();
        $response = $this->get(route('api.issues', ['overdue' => 'soon']));

        $response->assertJsonFragment([
            'id' => $dueTodayIssueLessTimeLeft->id
        ]);

        $response->assertJsonMissing([
            'id' => $dueTodayIssueMoreTimeLeft->id
        ]);

        $response->assertJsonMissing([
            'id' => $notDueTodayIssue->id
        ]);
    }

    /** @test */
    public function user_can_filter_issues_by_project()
    {
        // Given we have two projects
        $projectOne = create(Project::class);
        $projectTwo = create(Project::class);
        // Issue in project one
        $issueOne = create(Issue::class, ['project_id' => $projectOne->id]);
        // And issue in project two
        $issueTwo = create(Issue::class, ['project_id' => $projectTwo->id]);

        // When authenticated user makes request to get all issues in project one
        $this->signIn();
        $response = $this->get(route('api.issues', ['project' => $projectOne->id]));

        // Then response only contains issues in project one
        $response->assertJsonFragment([
            'id' => $issueOne->id
        ]);
        // And doesn't contain issues in project two
        $response->assertJsonMissing([
            'id' => $issueTwo->id
        ]);
    }

    /** @test */
    public function user_can_filter_issues_by_project_including_sub_projects()
    {
        // Given we have a project
        $projectOne = create(Project::class);
        // And project that is subproject of project one
        $projectTwo = create(Project::class, ['parent_id' => $projectOne->id]);
        // And another non related project
        $projectThree = create(Project::class);
        // Issue in project one
        $issueOne = create(Issue::class, ['project_id' => $projectOne->id]);
        // And issue in project two
        $issueTwo = create(Issue::class, ['project_id' => $projectTwo->id]);
        // And issue in project three
        $issueThree = create(Issue::class, ['project_id' => $projectThree->id]);

        // When authenticated user makes request to get all issues in project one including subprojects
        $this->signIn();
        $response = $this->get(route('api.issues', ['project' => $projectOne->id, 'include_subprojects' => 'yes']));

        // Then response only contains issues in project one
        $response->assertJsonFragment([
            'id' => $issueOne->id
        ]);
        // And contains issues in project two
        $response->assertJsonFragment([
            'id' => $issueTwo->id
        ]);
        // And doesn't contain issues in project three
        $response->assertJsonMissing([
            'id' => $issueThree->id
        ]);
    }
}
