<?php

namespace Tests\Feature;

use App\Facades\RedmineApi;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssuesTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");
    }

    protected function makeIssueAndTrackIt(array $attributes = []): array
    {
        $issue = $this->makeFakeIssueArray($attributes);
        $issueId = $issue['id'];

        RedmineApi::shouldReceive('getIssue')
            ->once()
            ->with($issueId)
            ->andReturn($issue);

        $this->signIn();
        $this->post(route('api.issues.track'), ['issue_id' => $issueId]);
        return $issue;
    }

    protected function createTrackedIssue(?User $user = null, array $attributes = [], bool $open = true): Issue
    {
        $state = $open ? 'open' : 'closed';
        $user = $user ?? factory(User::class)->create();
        $issue = factory(Issue::class)->states($state)->create($attributes);
        $issue->track($user);
        return $issue;
    }

}
