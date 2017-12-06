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
    public function it_calculates_time_left_to_solve_the_issue()
    {
        $service = Service::create([
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $issue = create('App\Models\Issue',[
            'service_id' => $service->id,
            'created_on' => Carbon::now()->addWeekdays(-2)
        ]);


    }
}
