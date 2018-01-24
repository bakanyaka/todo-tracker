<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use Carbon\Carbon;
use Tests\Feature\IssuesTestCase;

class IssueReportsTest extends IssuesTestCase
{
    protected function setUp()
    {
        parent::setUp();
        Carbon::setTestNow('2018-02-02 15:00:00');
    }

    /** @test */
    public function user_can_get_number_of_issues_created_within_given_period()
    {
        // Beginning of the month
        factory(Issue::class, 1)->states('open')->create([
            'created_on' => '2018-01-09 10:00:00'
        ]);
        // Week ago
        factory(Issue::class, 2)->states('open')->create([
            'created_on' => '2018-01-25 10:00:00'
        ]);
        // This week
        factory(Issue::class, 3)->states('open')->create([
            'created_on' => '2018-01-31 10:00:00'
        ]);


        $this->signIn();
        // Within month
        $response = $this->get(route('api.issues.reports', ['period' => 30]));
        $response->assertJsonFragment([
            'total' => 6
        ]);
        // Within 2 weeks
        $response = $this->get(route('api.issues.reports', ['period' => 14]));
        $response->assertJsonFragment([
            'total' => 5
        ]);
        // Within week
        $response = $this->get(route('api.issues.reports', ['period' => 7]));
        $response->assertJsonFragment([
            'total' => 3
        ]);
    }

    /** @test */
    public function user_can_get_number_of_issues_closed_within_given_period()
    {
        // Closed in beginning of the month
        factory(Issue::class, 2)->states('closed')->create([
            'created_on' => '2018-01-09 12:00:00',
            'closed_on' => '2018-01-09 15:00:00'
        ]);
        // Closed Week ago
        factory(Issue::class, 3)->states('closed')->create([
            'created_on' => '2018-01-22 12:00:00',
            'closed_on' => '2018-01-24 13:00:00'
        ]);
        // Closed This week
        factory(Issue::class, 2)->states('closed')->create([
            'created_on' => '2018-01-31 12:00:00',
            'closed_on' => '2018-01-31 15:00:00'
        ]);
        // Open issues that should not be included
        factory(Issue::class, 1)->states('open')->create([
            'created_on' => '2018-01-31 12:00:00'
        ]);

        $this->signIn();
        // Within month
        $response = $this->get(route('api.issues.reports', ['period' => 30]));
        $response->assertJsonFragment([
            'total' => 7
        ]);
        // Within 2 weeks
        $response = $this->get(route('api.issues.reports', ['period' => 14]));
        $response->assertJsonFragment([
            'total' => 5
        ]);
        // Within week
        $response = $this->get(route('api.issues.reports', ['period' => 7]));
        $response->assertJsonFragment([
            'total' => 2
        ]);
    }

    /** @test */
    public function user_can_get_number_of_issues_created_each_day_within_given_period()
    {
        factory(Issue::class, 1)->create([
            'created_on' => '2018-02-02 10:00:00'
        ]);
        factory(Issue::class, 3)->create([
            'created_on' => '2018-02-01 10:00:00'
        ]);
        factory(Issue::class, 2)->create([
            'created_on' => '2018-01-26 10:00:00'
        ]);
        $this->signIn();
        $response = $this->get(route('api.issues.reports', ['period' => 7]));
        $response->assertJsonFragment([
            'created' => [
                'total' => 5,
                'data' => [
                    [
                        'x' => '2018-01-26',
                        'y' => 2,
                    ],
                    [
                        'x' => '2018-01-27',
                        'y' => 0,
                    ],
                    [
                        'x' => '2018-01-28',
                        'y' => 0,
                    ],
                    [
                        'x' => '2018-01-29',
                        'y' => 0,
                    ],
                    [
                        'x' => '2018-01-30',
                        'y' => 0,
                    ],
                    [
                        'x' => '2018-01-31',
                        'y' => 0,
                    ],
                    [
                        'x' => '2018-02-01',
                        'y' => 3,
                    ],
                ]
            ]
        ]);
        //Current day should not be included
        $response->assertJsonMissing(
            [
                'x' => '2018-02-02',
                'y' => 1,
            ]
        );
    }
}
