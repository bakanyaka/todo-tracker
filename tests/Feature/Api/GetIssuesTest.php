<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
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
        //Then response only contains issue that was created within given interval
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
    public function user_can_get_all_issues_open_in_a_specified_period()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');
        // Given we have an issue created more than 7 days ag
        $oldIssue = create(Issue::class, [
            'created_on' => '2018-01-09 12:00:00'
        ]);
        // And an issue created within 7 days
        $newIssue = create(Issue::class, [
            'created_on' => '2018-01-13 10:00:00'
        ]);
        // When user makes request to get all open issues in 7 days
        $this->signIn();
        $response = $this->get(route('api.issues', ['period' => 7]));
        // Then response only contains issue created within 7 days
        $response->assertJsonMissing([
            'id' => $oldIssue->id
        ]);
        $response->assertJsonFragment([
            'id' => $newIssue->id
        ]);
    }

    /** @test */
    public function user_can_get_all_issues_closed_in_a_specified_period()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');
        // Given we have an issue closed more than 7 days ag
        $oldIssue = create(Issue::class, [
            'created_on' => '2018-01-09 12:00:00',
            'closed_on' => '2018-01-09 13:00:00'
        ]);
        // And an issue closed within 7 days
        $newIssue = factory(Issue::class)->states(['closed'])->create([
            'created_on' => '2018-01-09 10:00:00',
            'closed_on' => '2018-01-13 11:00:00'
        ]);
        // When user makes request to get all open issues in 7 days
        $this->signIn();
        $response = $this->get(route('api.issues', ['status' => 'closed', 'period' => 7]));
        // Then response only contains issue created within 7 days
        $response->assertJsonMissing([
            'id' => $oldIssue->id
        ]);
        $response->assertJsonFragment([
            'id' => $newIssue->id
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
}
