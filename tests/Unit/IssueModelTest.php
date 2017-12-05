<?php

namespace Tests\Unit;

use App\Facades\Redmine;
use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $issue->save();
    }
}
