<?php

namespace Tests\Feature\Api;

use App\Models\Issue;
use App\Models\Service;
use Carbon\Carbon;
use Tests\Feature\IssuesTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IssueStatsTest extends IssuesTestCase
{
    use RefreshDatabase;

    private function createTestData()
    {
        factory(Issue::class,15)->states('open')->create();
        factory(Issue::class,10)->states('closed')->create();
        factory(Issue::class,3)->states('paused')->create();
    }


    /** @test */
    public function user_can_get_count_of_open_issues()
    {
        $this->createTestData();
        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'open' => 18
        ]);
    }

    /** @test */
    public function user_can_get_count_of_paused_issues()
    {
        $this->createTestData();
        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'paused' => 3
        ]);
    }

    /** @test */
    public function user_can_get_count_of_overdue_issues()
    {
        Carbon::setTestNow('2018-01-19 15:00:00');
        $this->createTestData();
        //Overdue issues
        $service = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        factory(Issue::class,4)->create([
            'created_on' => Carbon::parse('2018-01-19 09:00:00'),
            'service_id' => $service->id
        ]);
        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'overdue' => 4
        ]);
    }

    /** @test */
    public function user_can_get_count_of_due_today_issues_that_have_less_than_30_percent_of_time_left()
    {
        Carbon::setTestNow('2018-01-19 10:00:00');

        $twoHoursService = create(Service::class, [
            'name' => 'Тестирование',
            'hours' => 2
        ]);
        $twentyFourHoursService = create(Service::class, [
            'name' => 'Разработка',
            'hours' => 24
        ]);
        //Due Today issues but have more than 30 percent of time left
        factory(Issue::class,2)->create([
            'created_on' => Carbon::now(),
            'service_id' => $twoHoursService->id
        ]);
        //Due Today issues and have less 30 percent of time left
        factory(Issue::class,4)->create([
            'created_on' => '2018-01-19 08:10:00',
            'service_id' => $twoHoursService->id
        ]);
        //Not Due Today Issues
        factory(Issue::class,3)->create([
            'created_on' => Carbon::now(),
            'service_id' => $twentyFourHoursService->id
        ]);

        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'due_soon' => 4
        ]);
    }

    /** @test */
    public function user_can_get_count_of_issues_created_today()
    {
        Carbon::setTestNow('2018-01-19 11:00:00');
        factory(Issue::class,2)->states('open')->create();
        factory(Issue::class,3)->states('closed')->create([
            'created_on' => '2018-01-19 09:00:00',
            'closed_on' => '2018-01-19 10:00:00'
        ]);
        factory(Issue::class,2)->states('closed')->create([
            'created_on' => '2018-01-18 09:00:00',
            'closed_on' => '2018-01-18 10:00:00'
        ]);

        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'created_today' => 5
        ]);
    }

    /** @test */
    public function user_can_get_count_of_issues_closed_today()
    {
        Carbon::setTestNow('2018-01-19 11:00:00');
        factory(Issue::class,2)->states('open')->create();
        factory(Issue::class,3)->states('closed')->create([
            'created_on' => '2018-01-19 09:00:00',
            'closed_on' => '2018-01-19 10:00:00'
        ]);
        factory(Issue::class,2)->states('closed')->create([
            'created_on' => '2018-01-18 09:00:00',
            'closed_on' => '2018-01-18 10:00:00'
        ]);

        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'closed_today' => 3
        ]);
    }

    /** @test */
    public function user_can_get_count_of_open_issues_in_procurement()
    {
        factory(Issue::class,1)->states('open')->create();
        factory(Issue::class,1)->states('closed')->create(['assigned_to' => 'Отдел Закупок']);
        factory(Issue::class,2)->states('open')->create(['assigned_to' => 'Отдел Закупок']);
        $this->signIn();
        $response = $this->get(route('api.issues.stats'));
        $response->assertJsonFragment([
            'in_procurement' => 2
        ]);
    }
}
