<?php

namespace Tests\Feature;

use App\BusinessDate;
use App\Models\Issue;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IssuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_all_tracked_issues()
    {
        $issue = create('App\Models\Issue');

        $response = $this->get(route('issues'));

        $response->assertStatus(200);
        $response->assertSee($issue->title);
        $response->assertSee((string)$issue->issue_id);
        $response->assertSee($issue->created_on->toDateTimeString());
    }

    /** @test */
    public function user_can_create_a_new_issue() {
        $issue = make('App\Models\Issue');
        $response = $this->post(route('issues'), [
            'title' => $issue->title,
            'issue_id' => $issue->issue_id,
            'created_on' => '2017-12-04 08:00',
            'estimated_hours' => 5
        ]);
        $this->assertDatabaseHas('issues', ['issue_id' => $issue->issue_id]);
    }

}
