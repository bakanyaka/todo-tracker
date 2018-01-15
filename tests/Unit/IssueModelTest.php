<?php

namespace Tests\Unit;

use App\BusinessDate;
use App\Facades\Redmine;
use App\Models\Issue;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;


class IssueModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        $this->artisan("db:Seed");
    }

    /** @test */
    public function can_get_priority_name()
    {
        $priority = \App\Models\Priority::create([
            'id' => 1,
            'name' => 'Высокий'
        ]);
        $issue = create('App\Models\Issue', [
            'priority_id' => $priority->id
        ]);

        $this->assertEquals('Высокий', $issue->priority->name);
    }

    /** @test */
    public function can_get_issue_paused_status_using_attribute()
    {
        $issue = create(Issue::class, [
            'status_id' => 4
        ]);

        $this->assertEquals(4,$issue->status->id);
        $this->assertEquals(true, $issue->is_paused);

    }

    /** @test */
    public function it_updates_model_data_from_redmine()
    {
        $issueData = $this->makeFakeIssueArray();
        $issue = new Issue(['id' => $issueData['id']]);

        Redmine::shouldReceive('getIssue')
            ->once()
            ->with($issueData['id'])
            ->andReturn($issueData);

        $issue->updateFromRedmine();
        $this->assertEquals($issueData['subject'], $issue->subject);
        $this->assertEquals($issueData['priority_id'], $issue->priority_id);
        $this->assertEquals($issueData['status_id'], $issue->status_id);
        $this->assertEquals($issueData['department'], $issue->department);
        $this->assertEquals($issueData['assigned_to'], $issue->assigned_to);
        $this->assertEquals($issueData['created_on'], $issue->created_on);
        $this->assertEquals($issueData['closed_on'], $issue->closed_on);
        $this->assertEquals(true, $issue->control);
        $this->assertEquals(24, $issue->estimatedHours);
    }

    /** @test */
    public function issue_can_not_be_tracked_by_same_user_twice()
    {
        //Given we have an issue tracked by user
        $user = create('App\User');
        $issue = create('App\Models\Issue');
        $issue->track($user);
        //When we try to track same issue by same user again
        $issue->track($user);
        //Then it doesn't get tracked again
        $recordsCount = $user->issues()->where(['issue_id' => $issue->id])->count();
        $this->assertEquals(1, $recordsCount);
    }

    /** @test */
    public function it_knows_if_it_is_tracked_by_user()
    {
        $user = create('App\User');
        $anotherUser = create('App\User');
        $issue = create('App\Models\Issue');
        $issue->track($user);

        $this->assertEquals(true, $issue->isTrackedBy($user));
        $this->assertEquals(false, $issue->isTrackedBy($anotherUser));

    }

    /** @test */
    public function it_calculates_due_date()
    {
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $issue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => '2017-12-05 15:00:00'
        ]);
        $this->assertEquals('2017-12-06 09:00:00', $issue->dueDate);
    }

    /** @test */
    public function it_calculates_time_left_to_solve_the_issue_or_overdue_time_while_issue_is_open()
    {
        $now = Carbon::create(2017, 12, 07, 12);
        Carbon::setTestNow($now);

        // Overdue issue should return negative value
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $overDueIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 8)
        ]);
        $this->assertEquals(-2, $overDueIssue->time_left);

        // On time issue should return positive value
        $onTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 11)
        ]);
        $this->assertEquals(1, $onTimeIssue->time_left);

        // Issue without due time should return null value
        $issueWithoutDueDate = create('App\Models\Issue', [
            'service_id' => null
        ]);
        $this->assertEquals(null, $issueWithoutDueDate->time_left);
    }

    /** @test */
    public function it_calculates_time_left_before_deadline_or_overdue_time_when_issue_is_closed()
    {

        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);

        // In time issue should return positive value
        $closedInTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 11),
            'closed_on' => Carbon::create(2017, 12, 07, 12)
        ]);
        $this->assertEquals(1, $closedInTimeIssue->time_left);

        // Overdue issue should return positive value
        $closedInTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 11),
            'closed_on' => Carbon::create(2017, 12, 07, 15)
        ]);
        $this->assertEquals(-2, $closedInTimeIssue->time_left);
    }

    /** @test */
    public function it_calculates_actual_time_since_issue_was_created_till_it_was_closed()
    {
        $issue = create('App\Models\Issue', [
            'created_on' => Carbon::create(2017, 12, 07, 11),
            'closed_on' => Carbon::create(2017, 12, 07, 15),
        ]);
        $this->assertEquals(4, $issue->actual_time);

        //Issue that is not closed should return null
        $issue = create('App\Models\Issue', [
            'created_on' => Carbon::create(2017, 12, 07, 11)
        ]);
        $this->assertEquals(null, $issue->actual_time);
    }

    /** @test */
    public function it_calculates_percent_of_time_left()
    {

        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 4
        ]);

        $now = Carbon::create(2017, 12, 07, 12);
        Carbon::setTestNow($now);

        $closedInTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 10),
        ]);
        $this->assertEquals(50, $closedInTimeIssue->percent_of_time_left);
    }

    /** @test */
    public function feedback_time_is_added_to_due_date()
    {
        //Given we have an issue with set feedback time
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $issue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => '2017-12-05 15:00:00',
            'on_feedback_hours' => 1.5
        ]);
        //When we retrieve due_date
        //Then due_date should include feedback time
        $this->assertEquals('2017-12-06 10:30:00', $issue->dueDate);
    }

    /** @test */
    public function when_issue_is_on_paused_status_time_left_is_calculated_based_on_last_status_change()
    {
        // Given we have an open issue that has paused status and status change timestamp
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $issue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'status_id' => 4,
            'created_on' => '2017-12-05 15:00:00',
            'status_changed_on' => '2017-12-05 16:00:00'
        ]);
        // When we retrieve issue time_left
        // Then it should be calculated on last status change timestamp instead of current time
        $this->assertEquals('1', $issue->time_left);
    }

    /** @test */
    public function status_changed_on_timestamp_is_updated_when_status_id_changed()
    {
        $now = Carbon::create(2017, 1, 15, 9);
        Carbon::setTestNow($now);

        // Given we have an issue with old status_changed_on timestamp
        $issue = create(Issue::class, [
            'status_id' => 1,
            'status_changed_on' => '2017-12-05 16:00:00'
        ]);
        $this->assertEquals('2017-12-05 16:00:00', (string)$issue->status_changed_on);
        // When we change issue status
        $issue->status_id = 4;
        $issue->save();
        $issue = $issue->fresh();
        // Then status_changed_on timestamp should be equal current timestamp
        $this->assertEquals(4,$issue->status_id);
        $this->assertEquals($now, $issue->status_changed_on);
    }

    /** @test */
    public function status_changed_on_timestamp_is_not_updated_when_new_status_id_is_the_same()
    {
        $now = Carbon::create(2017, 1, 15, 9);
        Carbon::setTestNow($now);

        // Given we have an issue with status_changed_on timestamp
        $issue = create(Issue::class, [
            'status_id' => 1,
            'status_changed_on' => '2017-12-05 16:00:00'
        ]);
        $this->assertEquals('2017-12-05 16:00:00', (string)$issue->status_changed_on);
        // When we change issue status to same status
        $issue->status_id = 1;
        $issue->save();
        //Then status_changed_on timestamp should be the same as before
        $issue = $issue->fresh();
        $this->assertEquals('2017-12-05 16:00:00', (string)$issue->status_changed_on);

    }


}
