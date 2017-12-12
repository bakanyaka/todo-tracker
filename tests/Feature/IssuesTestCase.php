<?php

namespace Tests\Feature;

use App\Facades\Redmine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssuesTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        $this->artisan("db:Seed", ['--class' => 'PrioritiesTableSeeder']);
    }


    /**
     * @param array $attributes
     * @return array
     */
    protected function makeIssueAndTrackIt($attributes = [])
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
