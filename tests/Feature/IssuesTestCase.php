<?php

namespace Tests\Feature;

use App\Facades\Redmine;
use App\Models\Issue;
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
        $this->post(route('api.issues.track'), ['issue_id' => $issueId]);
        return $issue;
    }

    /**
     * @param \App\User $user
     * @param array $attributes
     * @param bool $open
     * @return Issue
     */
    protected function createTrackedIssue($user = null, $attributes = [], $open = true)
    {
        $state = $open ? 'open' : 'closed';
        $user = $user ? $user : create('App\User');
        $issue = factory(Issue::class)->states($state)->create($attributes);
        $issue->track($user);
        return $issue;
    }

}
