<?php

namespace Tests\Feature;

use App\Models\Issue;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewIssuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_all_tracked_issues()
    {
        $issue = Issue::create([
            'title' => 'Пупкин Василий Иванович: замена ПК',
            'issue_id' => 3532,
            'created_on' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(10)
        ]);

        $response = $this->get(route('issues'));
        $response->assertStatus(200);
        $response->assertSee($issue->title);
        $response->assertSee((string)$issue->issue_id);
        $response->assertSee($issue->created_on->toDateTimeString());
    }


}
