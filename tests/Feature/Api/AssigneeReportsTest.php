<?php

namespace Tests\Feature\Api;

use App\Models\Assignee;
use App\Models\Issue;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssigneeReportsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_number_of_issues_that_assignees_spent_time_on_for_all_assignees_for_the_given_period()
    {
        Carbon::setTestNow('2018-03-12 15:00:00');

        // Given we have an assignee
        $firstAssignee = create(Assignee::class, ['lastname' => 'Петров']);
        $issues = create(Issue::class, [
            'assigned_to_id' => $firstAssignee->id
        ], 3);

        // Time entry within given period indicating that he spent time on this issue
        create(TimeEntry::class, ['issue_id' => $issues[0]->id, 'assignee_id' => $firstAssignee->id, 'spent_on' => '2018-03-05']);
        create(TimeEntry::class, ['issue_id' => $issues[1]->id, 'assignee_id' => $firstAssignee->id, 'spent_on' => '2018-03-11']);
        // Second time entry for same issue that should be counted only once
        create(TimeEntry::class, ['issue_id' => $issues[1]->id, 'assignee_id' => $firstAssignee->id, 'spent_on' => '2018-03-11']);
        // Time entry before given period that should not be counted
        create(TimeEntry::class, ['issue_id' => $issues[2]->id, 'assignee_id' => $firstAssignee->id, 'spent_on' => '2018-03-04']);
        // And time entry after given period that should not be counted
        create(TimeEntry::class, ['issue_id' => $issues[2]->id, 'assignee_id' => $firstAssignee->id, 'spent_on' => '2018-03-12']);

        // And assignee with no issues
        $noIssuesAssignee = create(Assignee::class, ['lastname' => 'Васильев']);

        // When user makes request to get number of closed issues that assignee spent time on
        $this->signIn();
        $response = $this->get(route('api.assignees.report.index', [
            'period_from_date' => '2018-03-05',
            'period_to_date' => '2018-03-11'
        ]));

        // Then Response contains correct number of closed issues for each assignee and is sorted by lastname
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                [
                    'id' => $noIssuesAssignee->id,
                    'spent_issues_count' => 0
                ],
                [
                    'id' => $firstAssignee->id,
                    'spent_issues_count' => 2
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_get_participated_issues_report_for_all_assignees_for_the_given_period()
    {
        Carbon::setTestNow('2018-03-15 15:00:00');

        // Given we have three assignees
        $firstAssignee = create(Assignee::class, ['lastname' => 'Иванов']);
        $secondAssignee = create(Assignee::class, ['lastname' => 'Петров']);     // And assignee with no issues
        $noIssuesAssignee = create(Assignee::class, ['lastname' => 'Васильев']);

        // Time entries within given period indicating that assignees spent time on issues

        // First assignee spent time on the issue, it is assigned to him and closed - should be counted once
        create(TimeEntry::class, [
            'issue_id' => factory(Issue::class)->states('closed')->create(['assigned_to_id' => $firstAssignee->id, 'created_on' => '2018-03-07'])->id,
            'assignee_id' => $firstAssignee->id,
            'spent_on' => '2018-03-07'
        ]);
        // First assignee spent time on the issue, it is assigned to him and open - should be counted twice
        create(TimeEntry::class, [
            'issue_id' => factory(Issue::class)->create(['assigned_to_id' => $firstAssignee->id, 'created_on' => '2018-03-07'])->id,
            'assignee_id' => $firstAssignee->id,
            'spent_on' => '2018-03-07'
        ]);
        // First assignee spent time on the issue, but it is assigned to second assignee and closed - should be counted once for both assignees
        create(TimeEntry::class, [
            'issue_id' => factory(Issue::class)->states('closed')->create(['assigned_to_id' => $secondAssignee->id, 'created_on' => '2018-03-07'])->id,
            'assignee_id' => $firstAssignee->id,
            'spent_on' => '2018-03-11'
        ]);

        // Second assignee has multiple time entries for same issue, it is assigned to him and closed - should be counted once
        create(TimeEntry::class, [
            'issue_id' => factory(Issue::class)->states('closed')->create(['assigned_to_id' => $secondAssignee->id, 'created_on' => '2018-03-07'])->id,
            'assignee_id' => $secondAssignee->id,
            'spent_on' => '2018-03-07'
        ], 5);
        // Second assignee spent time on the issue, it is assigned to him and paused - should be counted once
        create(TimeEntry::class, [
            'issue_id' => factory(Issue::class)->states('paused')->create(['assigned_to_id' => $secondAssignee->id,  'created_on' => '2018-03-07'])->id,
            'assignee_id' => $secondAssignee->id,
            'spent_on' => '2018-03-07'
        ]);

        // Also some issues without time-entries
        // Second assignee have issue created more than 2 days ago should be counted
        create(Issue::class, ['assigned_to_id' => $secondAssignee->id, 'created_on' => '2018-03-07']);

        // Third assignee have issue created 1 day ago. should not be counted
        factory(Issue::class)->create(['assigned_to_id' => $noIssuesAssignee->id, 'created_on' => '2018-03-14']);


        // When user makes request to get number of closed issues that assignee spent time on
        $this->signIn();

        $response = $this->get(route('api.assignees.report.index', [
            'period_from_date' => '2018-03-05',
            'period_to_date' => '2018-03-11'
        ]));

        // Then Response contains correct number of participated issues for each assignee
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                [
                    'id' => $noIssuesAssignee->id,
                    'participated_issues_count' => 0
                ],
                [
                    'id' => $firstAssignee->id,
                    'participated_issues_count' => 4
                ],
                [
                    'id' => $secondAssignee->id,
                    'participated_issues_count' => 3
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_get_a_list_of_issues_that_assignee_spent_time_on_in_the_given_period()
    {
        Carbon::setTestNow('2018-03-15 15:00:00');

        // Given we have an assignee
        $assignee = create(Assignee::class, ['firstname' => 'Иван', 'lastname' => 'Петров']);
        $issues = create(Issue::class, ['assigned_to_id' => $assignee->id], 2);
        $excluded = create(Issue::class, ['assigned_to_id' => $assignee->id], 2);

        // Time entry within given period indicating that he spent time on this issue
        create(TimeEntry::class, ['issue_id' => $issues[0]->id, 'assignee_id' => $assignee->id, 'spent_on' => '2018-03-05']);
        create(TimeEntry::class, ['issue_id' => $issues[1]->id, 'assignee_id' => $assignee->id, 'spent_on' => '2018-03-11']);
        // Second time entry for same issue that should be counted only once
        create(TimeEntry::class, ['issue_id' => $issues[1]->id, 'assignee_id' => $assignee->id, 'spent_on' => '2018-03-11']);
        // And time entry before given period that should not be counted
        create(TimeEntry::class, ['issue_id' => $excluded[0]->id, 'assignee_id' => $assignee->id, 'spent_on' => '2018-02-11']);
        // And time entry after given period that should not be counted
        create(TimeEntry::class, ['issue_id' => $excluded[1]->id, 'assignee_id' => $assignee->id, 'spent_on' => '2018-03-12']);

        // When user makes request to get number of closed issues that assignee spent time on
        $this->signIn();
        $response = $this->get(route('api.assignees.report.show', [
            'assignee' => $assignee,
            'period_from_date' => '2018-03-05',
            'period_to_date' => '2018-03-11'
        ]));

        $this->assertCount(2, $response->json('data')['issues']['spent']);
        $issues = $issues->sortBy('id')->values();
        $response->assertJson([
            'data' => [
                'id' => $assignee->id,
                'issues' => [
                    'spent' => [
                        ['id' => $issues[0]->id],
                        ['id' => $issues[1]->id],
                    ]
                ]
            ]
        ]);
    }
}
