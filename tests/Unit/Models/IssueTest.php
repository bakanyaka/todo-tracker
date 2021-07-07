<?php

namespace Tests\Unit\Models;

use App\Enums\OverdueState;
use App\Facades\RedmineApi;
use App\Models\Issue;
use App\Models\Priority;
use App\Models\Service;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class IssueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");
    }

    /** @test */
    public function it_belongs_to_priority()
    {
        $issue = create(Issue::class);
        $this->assertInstanceOf(Priority::class, $issue->priority);
    }

    /** @test */
    public function it_belongs_to_a_status()
    {
        $issue = create(Issue::class);
        $this->assertInstanceOf(Status::class, $issue->status);
    }

    /** @test */
    public function it_gets_is_paused_attribute()
    {
        $issue = factory(Issue::class)->state('paused')->create()->fresh();
        $this->assertInstanceOf(Status::class, $issue->status);
        $this->assertEquals(true, $issue->is_paused);
    }

    /** @test */
    public function issue_can_not_be_tracked_by_same_user_twice()
    {
        //Given we have an issue tracked by user
        $user = create('App\Models\User');
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
        $user = create('App\Models\User');
        $anotherUser = create('App\Models\User');
        $issue = create('App\Models\Issue');
        $issue->track($user);
        $this->assertEquals(true, $issue->isTrackedBy($user));
        $this->assertEquals(false, $issue->isTrackedBy($anotherUser));
    }

    /** @test */
    public function it_calculates_due_date()
    {
        $service = factory(Service::class)->create([
            'hours' => 2,
        ]);
        $issue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => '2017-12-05 15:00:00',
        ]);
        $this->assertEquals('2017-12-06 09:00:00', $issue->dueDate);
    }

    /** @test */
    public function it_calculates_time_left_to_solve_the_issue_or_overdue_time_while_issue_is_open()
    {
        $now = Carbon::create(2017, 12, 07, 12);
        Carbon::setTestNow($now);

        // Overdue issue should return negative value
        $service = factory(Service::class)->create([
            'hours' => 2,
        ]);
        $overDueIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 8),
        ]);
        $this->assertEquals(-2, $overDueIssue->time_left);

        // On time issue should return positive value
        $onTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 11),
        ]);
        $this->assertEquals(1, $onTimeIssue->time_left);

        // Issue without due time should return null value
        $issueWithoutDueDate = create('App\Models\Issue', [
            'service_id' => null,
        ]);
        $this->assertEquals(null, $issueWithoutDueDate->time_left);
    }

    /** @test */
    public function it_calculates_time_left_before_deadline_or_overdue_time_when_issue_is_closed()
    {
        $service = factory(Service::class)->create([
            'hours' => 2,
        ]);

        // In time issue should return positive value
        $closedInTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 11),
            'closed_on' => Carbon::create(2017, 12, 07, 12),
        ]);
        $this->assertEquals(1, $closedInTimeIssue->time_left);

        // Overdue issue should return positive value
        $closedInTimeIssue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017, 12, 07, 11),
            'closed_on' => Carbon::create(2017, 12, 07, 15),
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
            'created_on' => Carbon::create(2017, 12, 07, 11),
        ]);
        $this->assertEquals(null, $issue->actual_time);
    }

    /** @test */
    public function it_calculates_percent_of_time_left()
    {
        $service = factory(Service::class)->create([
            'hours' => 4,
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
        $service = factory(Service::class)->create([
            'hours' => 2,
        ]);
        $issue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'created_on' => '2017-12-05 15:00:00',
            'on_pause_hours' => 1.5,
        ]);
        //When we retrieve due_date
        //Then due_date should include feedback time
        $this->assertEquals('2017-12-06 10:30:00', $issue->dueDate);
    }

    /** @test */
    public function when_issue_is_on_paused_status_time_left_is_calculated_based_on_last_status_change()
    {
        // Given we have an open issue that has paused status and status change timestamp
        $service = factory(Service::class)->create([
            'name' => 'Тестирование',
            'hours' => 2,
        ]);
        $issue = create('App\Models\Issue', [
            'service_id' => $service->id,
            'status_id' => 4,
            'created_on' => '2017-12-05 15:00:00',
            'status_changed_on' => '2017-12-05 16:00:00',
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
            'status_changed_on' => '2017-12-05 16:00:00',
        ]);
        $this->assertEquals('2017-12-05 16:00:00', (string) $issue->status_changed_on);
        // When we change issue status
        $issue->status_id = 4;
        $issue->save();
        $issue = $issue->fresh();
        // Then status_changed_on timestamp should be equal current timestamp
        $this->assertEquals(4, $issue->status_id);
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
            'status_changed_on' => '2017-12-05 16:00:00',
        ]);
        $this->assertEquals('2017-12-05 16:00:00', (string) $issue->status_changed_on);
        // When we change issue status to same status
        $issue->status_id = 1;
        $issue->save();
        //Then status_changed_on timestamp should be the same as before
        $issue = $issue->fresh();
        $this->assertEquals('2017-12-05 16:00:00', (string) $issue->status_changed_on);
    }

    /** @test */
    public function when_status_changes_from_paused_to_active_time_passed_since_last_status_change_should_be_added_to_on_pause_hours(
    )
    {
        $now = Carbon::create(2018, 1, 15, 12);
        Carbon::setTestNow($now);

        // Given we have an issue on one of the paused statuses
        $issue = create(Issue::class, [
            'status_id' => 4,
            'status_changed_on' => '2018-01-15 10:00:00',
        ]);

        // When we set issue status to active status
        $issue->status_id = 2;
        $issue->save();
        $issue = $issue->fresh();
        // Then time passed (in hours) since previous status change should be added to om_pause_hours attribute
        $this->assertEquals(2, $issue->on_pause_hours);
    }

    /** @test */
    public function it_gets_overdue_state()
    {
        $this->travelTo(Carbon::now()->setHour(9)->setMinute(0));
        $service = factory(Service::class)->create(['hours' => 6]);
        $issue = factory(Issue::class)->create(['service_id' => $service->id]);

        $this->assertEquals(OverdueState::No, $issue->fresh()->getOverDueState());

        $this->travel(5)->hours();
        $this->assertEquals(OverdueState::Soon, $issue->fresh()->getOverDueState());

        $this->travel(2)->hours();
        $this->assertEquals(OverdueState::Yes, $issue->fresh()->getOverDueState());

        $issue->update(['status_id' => 4]);
        $this->assertEquals(OverdueState::No, $issue->fresh()->getOverDueState());
    }
}
