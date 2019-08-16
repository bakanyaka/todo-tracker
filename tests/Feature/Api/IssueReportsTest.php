<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Service;
use Carbon\Carbon;
use Tests\Feature\IssuesTestCase;

class IssueReportsTest extends IssuesTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->signIn();
        Carbon::setTestNow('2018-02-02 15:00:00');
    }

    /** @test */
    public function user_can_get_number_of_issues_created_each_day_within_given_period()
    {
        factory(Issue::class, 1)->create([
            'created_on' => '2018-01-25 00:00:00'
        ]);
        factory(Issue::class, 3)->create([
            'created_on' => '2018-01-27 10:00:00'
        ]);
        factory(Issue::class, 2)->create([
            'created_on' => '2018-01-31 23:59:00'
        ]);
        // Should not be included
        create(Issue::class, ['created_on' => '2018-01-24 10:00:00'], 5);
        create(Issue::class, ['created_on' => '2018-01-01 10:00:00'], 5);
        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31'
        ]));
        $response->assertJson([
            'data' => [
                'created' => [
                    'total' => 6,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 2,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 3,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 1,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_get_number_of_issues_closed_each_day_within_given_period()
    {
        factory(Issue::class, 1)->states('closed')->create([
            'closed_on' => '2018-01-25 00:00:00'
        ]);
        factory(Issue::class, 3)->states('closed')->create([
            'closed_on' => '2018-01-27 10:00:00'
        ]);
        factory(Issue::class, 2)->states('closed')->create([
            'closed_on' => '2018-01-31 23:59:00'
        ]);

        // Out of range dates should not be included
        factory(Issue::class, 5)->states('closed')->create([
            'closed_on' => '2018-01-24 23:59:00'
        ]);
        factory(Issue::class, 5)->states('closed')->create([
            'closed_on' => '2018-01-01 23:59:00'
        ]);

        // Open issues that should not be included
        factory(Issue::class, 5)->states('open')->create([
            'created_on' => '2018-01-27 12:00:00'
        ]);

        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31'
        ]));

        $response->assertJson([
            'data' => [
                'closed' => [
                    'total' => 6,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 2,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 3,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 1,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_get_number_of_overdue_closed_issues_each_day_within_given_period()
    {
        $twoHoursService = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);

        //Closed overdue issues
        factory(Issue::class, 3)->states('closed')->create([
            'created_on' => '2018-01-25 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-25 16:00:00'
        ]);
        factory(Issue::class, 2)->states('closed')->create([
            'created_on' => '2018-01-01 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-26 13:00:00'
        ]);
        factory(Issue::class, 1)->states('closed')->create([
            'created_on' => '2018-01-01 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-31 13:00:00'
        ]);
        //Closed in time issue should not be included
        factory(Issue::class, 1)->states('closed')->create([
            'created_on' => '2018-01-31 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-31 11:00:00'
        ]);

        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31'
        ]));

        $response->assertJson([
            'data' => [
                'closed_overdue' => [
                    'total' => 6,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 1,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 2,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 3,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_get_number_of_not_overdue_closed_issues_each_day_within_given_period()
    {
        $twoHoursService = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);

        //Closed overdue issues
        factory(Issue::class, 3)->states('closed')->create([
            'created_on' => '2018-01-01 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-25 13:00:00'
        ]);

        //Closed in time issues
        factory(Issue::class, 2)->states('closed')->create([
            'created_on' => '2018-01-25 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-25 11:00:00'
        ]);
        factory(Issue::class, 1)->states('closed')->create([
            'created_on' => '2018-01-31 10:00:00',
            'service_id' => $twoHoursService->id,
            'closed_on' => '2018-01-31 12:00:00'
        ]);

        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31'
        ]));

        $response->assertJson([
            'data' => [
                'closed_in_time' => [
                    'total' => 3,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 1,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 2,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function user_can_get_number_of_issues_closed_on_first_line_each_day_within_given_period()
    {

        factory(Issue::class, 3)->states('closed')->create([
            'status_id' => 8,
            'closed_on' => '2018-01-25 10:00:00'
        ]);
        factory(Issue::class, 2)->states('closed')->create([
            'status_id' => 8,
            'closed_on' => '2018-01-31 10:00:00'
        ]);
        // Closed not on first line should not be included
        factory(Issue::class, 1)->states('closed')->create([
            'closed_on' => '2018-01-27 10:00:00'
        ]);

        // Outside of period should not be included
        factory(Issue::class, 5)->states('closed')->create([
            'status_id' => 8,
            'closed_on' => '2018-02-01 10:00:00'
        ]);
        factory(Issue::class, 5)->states('closed')->create([
            'status_id' => 8,
            'closed_on' => '2018-01-24 10:00:00'
        ]);

        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31'
        ]));
        $response->assertJson([
            'data' => [
                'closed_first_line' => [
                    'total' => 5,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 2,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 3,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_be_filtered_by_project()
    {
        $projectOne = create(Project::class);
        $projectTwo = create(Project::class);
        $subProjectOfProjectTwo = create(Project::class, ['parent_id' => $projectTwo->id]);

        factory(Issue::class, 6)->create([
            'created_on' => '2018-01-27 10:00:00',
            'project_id' => $projectOne,
        ]);
        factory(Issue::class, 1)->create([
            'created_on' => '2018-01-27 10:00:00',
            'project_id' => $projectTwo,
        ]);
        factory(Issue::class, 1)->create([
            'created_on' => '2018-01-27 10:00:00',
            'project_id' => $subProjectOfProjectTwo,
        ]);

        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31',
            'project_id' => $projectTwo->id,
        ]));
        $response->assertJson([
            'data' => [
                'created' => [
                    'total' => 2,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 2,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 0,
                        ],
                    ]
                ]
            ]
        ]);

    }

    /** @test */
    public function it_can_be_filtered_by_tracker()
    {
        $trackerOne = create(Project::class);
        $trackerTwo = create(Project::class);

        factory(Issue::class, 2)->create([
            'created_on' => '2018-01-27 10:00:00',
            'tracker_id' => $trackerOne,
        ]);
        factory(Issue::class, 3)->create([
            'created_on' => '2018-01-27 10:00:00',
            'tracker_id' => $trackerTwo,
        ]);


        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31',
            'tracker_id' => $trackerOne->id,
        ]));
        $response->assertJson([
            'data' => [
                'created' => [
                    'total' => 2,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 2,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 0,
                        ],
                    ]
                ]
            ]
        ]);

    }

    /** @test */
    public function it_can_be_filtered_to_only_include_issues_with_service()
    {

        factory(Issue::class, 2)->create([
            'created_on' => '2018-01-27 10:00:00',
        ]);
        factory(Issue::class, 3)->create([
            'created_on' => '2018-01-27 10:00:00',
            'service_id' => null,
        ]);


        $response = $this->get(route('api.issues.reports', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31',
            'has_service' => 1,
        ]));
        $response->assertJson([
            'data' => [
                'created' => [
                    'total' => 2,
                    'data' => [
                        [
                            'x' => '2018-01-31',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-30',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-29',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-28',
                            'y' => 0,
                        ],
                        [
                            'x' => '2018-01-27',
                            'y' => 2,
                        ],
                        [
                            'x' => '2018-01-26',
                            'y' => 0,
                        ],

                        [
                            'x' => '2018-01-25',
                            'y' => 0,
                        ],
                    ]
                ]
            ]
        ]);

    }

    /** @test */
    public function user_can_get_issues_report_grouped_by_project()
    {
        $service = create(Service::class, ['hours' => 1]);
        $projectOne = create(Project::class);
        $projectTwo = create(Project::class);
        $subProjectOfProjectTwo = create(Project::class, ['parent_id' => $projectTwo->id]);
        $subProjectOfSubbrojectOfProjectTwo = create(Project::class, ['parent_id' => $subProjectOfProjectTwo->id]);

        factory(Issue::class, 2)->states('closed')->create([
            'created_on' => '2018-01-25 00:00:00',
            'closed_on' => '2018-01-25 00:00:00',
            'project_id' => $projectOne->id,
            'service_id' => $service->id
        ]);
        factory(Issue::class, 1)->states('open')->create([
            'created_on' => '2018-01-27 10:00:00',
            'project_id' => $projectOne->id
        ]);
        factory(Issue::class, 3)->states('closed')->create([
            'created_on' => '2018-01-26 10:00:00',
            'closed_on' => '2018-01-26 13:00:00',
            'project_id' => $projectTwo->id,
            'service_id' => $service->id
        ]);
        factory(Issue::class, 2)->states('open')->create([
            'created_on' => '2018-01-31 23:59:00',
            'project_id' => $projectTwo->id
        ]);
        factory(Issue::class, 1)->states('open')->create([
            'created_on' => '2018-01-26 10:00:00',
            'project_id' => $subProjectOfProjectTwo->id
        ]);
        factory(Issue::class, 1)->states('closed')->create([
            'created_on' => '2018-01-26 10:00:00',
            'closed_on' => '2018-01-26 13:00:00',
            'service_id' => $service->id,
            'project_id' => $subProjectOfProjectTwo->id
        ]);
        factory(Issue::class, 1)->states('open')->create([
            'created_on' => '2018-01-26 10:00:00',
            'project_id' => $subProjectOfSubbrojectOfProjectTwo->id
        ]);

        $response = $this->get(route('api.issues.reports.projects', [
            'period_from_date' => '2018-01-25',
            'period_to_date' => '2018-01-31'
        ]));

        $response->assertJson([
            'data' => [
                [
                    'project' => $projectTwo->name,
                    'project_id' => $projectTwo->id,
                    'parent_project_id' => $projectTwo->parent_id,
                    'children' => [
                        [
                            'project' => $subProjectOfProjectTwo->name,
                            'project_id' => $subProjectOfProjectTwo->id,
                            'parent_project_id' => $subProjectOfProjectTwo->parent_id,
                            'children' => [],
                            'created' => 3,
                            'closed' => 1,
                            'closed_in_time' => 0,
                            'closed_overdue' => 1
                        ]
                    ],
                    'created' => 8,
                    'closed' => 4,
                    'closed_in_time' => 0,
                    'closed_overdue' => 4
                ],
                [
                    'project' => $projectOne->name,
                    'project_id' => $projectOne->id,
                    'parent_project_id' => $projectOne->parent_id,
                    'children' => [],
                    'created' => 3,
                    'closed' => 2,
                    'closed_in_time' => 2,
                    'closed_overdue' => 0
                ],
            ]
        ]);
    }
}
