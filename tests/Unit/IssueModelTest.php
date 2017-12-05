<?php

namespace Tests\Unit;

use App\Facades\Redmine;
use App\Models\Issue;
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
        $this->assertEquals(24,$issue->getEstimatedHours());
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
        $recordsCount = DB::table('issue_user')->where(['user_id' => $user->id, 'issue_id' => $issue->id])->count();
        $this->assertEquals(1, $recordsCount);
    }
}
