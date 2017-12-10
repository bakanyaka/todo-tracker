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

        $this->assertEquals('Высокий',$issue->priority->name);
    }

    /** @test */
    public function issue_is_created_with_default_priority_id_when_no_priority_specified()
    {
        $issue = create('App\Models\Issue');
        $issue = $issue->fresh();
        $this->assertEquals(4,$issue->priority_id);
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
        $this->assertEquals($issueData['subject'],$issue->subject);
        $this->assertEquals($issueData['priority_id'],$issue->priority_id);
        $this->assertEquals($issueData['department'],$issue->department);
        $this->assertEquals($issueData['created_on'],$issue->created_on);
        $this->assertEquals($issueData['closed_on'],$issue->closed_on);
        $this->assertEquals(24,$issue->estimatedHours);
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
    public function it_calculates_due_date()
    {
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $issue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => '2017-12-05 15:00:00'
        ]);
        $this->assertEquals('2017-12-06 09:00:00',$issue->dueDate);
    }

    /** @test */
    public function it_calculates_time_left_to_solve_the_issue_or_overdue_time_while_issue_is_open()
    {
        $now = Carbon::create(2017,12,07,12);
        Carbon::setTestNow($now);

        // Overdue issue should return negative value
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $overDueIssue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017,12,07,8)
        ]);
        $this->assertEquals(-2, $overDueIssue->time_left);

        // On time issue should return positive value
        $onTimeIssue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017,12,07,11)
        ]);
        $this->assertEquals(1, $onTimeIssue->time_left);

        // Issue without due time should return null value
        $issueWithoutDueDate = create('App\Models\Issue',[
            'service_id' => null
        ]);
        $this->assertEquals(null,$issueWithoutDueDate->time_left);
    }

    /** @test */
    public function it_calculates_time_left_before_deadline_or_overdue_time_when_issue_is_closed()
    {

        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);

        // In time issue should return positive value
        $closedInTimeIssue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017,12,07,11),
            'closed_on' => Carbon::create(2017,12,07,12)
        ]);
        $this->assertEquals(1, $closedInTimeIssue->time_left);

        // Overdue issue should return positive value
        $closedInTimeIssue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017,12,07,11),
            'closed_on' => Carbon::create(2017,12,07,15)
        ]);
        $this->assertEquals(-2, $closedInTimeIssue->time_left);
    }

    /** @test */
    public function it_calculates_actual_time_since_issue_was_created_till_it_was_closed()
    {
        $issue = create('App\Models\Issue',[
            'created_on' => Carbon::create(2017,12,07,11),
            'closed_on' => Carbon::create(2017,12,07,15),
        ]);
        $this->assertEquals(4, $issue->actual_time);

        //Issue that is not close should return null
        $issue = create('App\Models\Issue',[
            'created_on' => Carbon::create(2017,12,07,11)
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

        $now = Carbon::create(2017,12,07,12);
        Carbon::setTestNow($now);

        $closedInTimeIssue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => Carbon::create(2017,12,07,10),
        ]);
        $this->assertEquals(50, $closedInTimeIssue->percent_of_time_left);
    }
}
