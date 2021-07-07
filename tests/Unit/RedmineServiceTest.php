<?php

namespace Tests\Unit;

use App\Services\RedmineApiService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RedmineServiceTest extends TestCase
{

    /** @test */
    public function it_retrieves_issue_by_id_using_redmine_api()
    {
        $issue = $this->makeFakeRedmineIssue();
        $issueData = $issue['issue'];
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'], json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new RedmineApiService($client);
        $result = $redmine->getIssue(324);
        $this->assertEquals([
            'id' => $issueData['id'],
            'parent_id' => $issueData['parent']['id'],
            'project_id' => $issueData['project']['id'],
            'tracker_id' => $issueData['tracker']['id'],
            'status_id' => $issueData['status']['id'],
            'priority_id' => $issueData['priority']['id'],
            'author' => $issueData['author']['name'],
            'author_id' => $issueData['author']['id'],
            'assigned_to' => $issueData['assigned_to']['name'],
            'subject' => $issueData['subject'],
            'description' => $issueData['description'],
            'service_id' => $issueData['custom_fields'][0]['value'],
            'start_date' => Carbon::parse($issueData['start_date']),
            'due_date' => Carbon::parse($issueData['due_date']),
            'created_on' => Carbon::parse($issueData['created_on']),
            'updated_on' => Carbon::parse($issueData['updated_on']),
            'closed_on' => Carbon::parse($issueData['closed_on']),
            'assigned_to_id' => $issueData['assigned_to']['id'],
        ], $result);
    }

    /**
     * @param  array  $attributes
     * @return array
     */
    protected function makeFakeRedmineIssue($attributes = [])
    {
        $issue = [
            'issue' => array_merge([
                'id' => $this->faker->unique()->randomNumber(5),
                'project' => [
                    'id' => 90,
                    'name' => 'Служба технической поддержки МЗ Арсенал'
                ],
                'tracker' => [
                    'id' => 3,
                    'name' => 'Поддержка'
                ],
                'status' => [
                    'id' => 2,
                    'name' => 'Назначен'
                ],
                'priority' => [
                    'id' => 4,
                    'name' => 'Нормальный'
                ],
                'author' => [
                    'id' => $this->faker->randomNumber(3),
                    'name' => $this->faker->name
                ],
                'assigned_to' => [
                    'id' => $this->faker->randomNumber(3),
                    'name' => $this->faker->name
                ],
                'parent' => [
                    'id' => 71223
                ],
                'subject' => $this->faker->name.' : '.$this->faker->realText(60),
                'description' => $this->faker->realText(),
                'start_date' => $start = $this->faker->dateTimeThisMonth()->format('Y-m-d'),
                'due_date' => Carbon::parse($start)->addDays($this->faker->numberBetween(1, 30))->format('Y-m-d'),
                'done_ratio' => $this->faker->numberBetween(0, 100),
                'custom_fields' => [
                    [
                        'id' => 80,
                        'name' => 'Сервисный запрос',
                        'value' => '9'
                    ],
                ],
                'created_on' => $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z'),
                'updated_on' => $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z'),
                'closed_on' => $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z')
            ], $attributes)
        ];
        return $issue;
    }

    /** @test */
    public function it_retrieves_all_issues_modified_since_given_date()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/issues_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/issues_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new RedmineApiService($client);

        $result = $redmine->getUpdatedIssues(Carbon::parse('2017-12-13'));

        $this->assertCount(45, $result);
        $this->assertArraySubset([
            "id" => 62961,
            "project_id" => 144,
            "tracker_id" => 2,
            "status_id" => 1,
            "priority_id" => 4,
            "author" => "Инесса Канунник",
            "author_id" => 162,
            "assigned_to" => "Тех.поддержка Омега",
            "assigned_to_id" => 196,
            "subject" => 'Шеварденкова Татьяна Геннадьевна:предоставить доступ  (31-26, 292-40-81 факс)',
            "description" => "Направляю Вам заявку на изменение полномочий пользователя ЛВС начальника ИМС-главного метролога Силантьева В.А.",
            "service_id" => 20,
            'created_on' => Carbon::parse('2019-10-15T13:05:34Z')->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse('2019-10-15T13:05:34Z')->timezone('Europe/Moscow'),
            'closed_on' => null
        ], $result[0]);
        $this->assertArraySubset([
            "id" => 62954,
            "project_id" => 93,
            "tracker_id" => 24,
            "status_id" => 2,
            "priority_id" => 4,
            "author" => "Инесса Канунник",
            "assigned_to" => "Тех. поддержка (2 lvl)",
            "assigned_to_id" => 101,
            "subject" => 'Фиалковская Елена Владимировна: лже-замятие бумаги  (72-26)',
            "service_id" => 14,
            'created_on' => Carbon::parse('2019-10-15T11:13:14Z')->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse('2019-10-16T05:41:53Z')->timezone('Europe/Moscow'),
            'closed_on' => null
        ], $result[2]);
    }

    /** @test */
    public function it_retrieves_all_time_entries_since_given_date()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/time_entries_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/time_entries_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new RedmineApiService($client);

        $result = $redmine->getTimeEntries(Carbon::parse('2017-12-13'));

        $this->assertCount(50, $result);
        $this->assertArraySubset([
            "id" => 75989,
            "assignee_id" => 83,
            "project_id" => 90,
            "issue_id" => 42584,
            "hours" => 0.2,
            "comments" => "Boo!",
            "spent_on" => Carbon::parse("2018-03-02"),
        ], $result[0]);

    }

    /** @test */
    public function it_retrieves_all_users()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/users_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/users_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new RedmineApiService($client);

        $result = $redmine->getUsers();

        $this->assertCount(43, $result);
        $this->assertArraySubset([
            "id" => 14,
            "login" => "a.mashkin",
            "firstname" => "Александр",
            "lastname" => "Машкин",
            "mail" => "mav58073@arsenal.plm",
        ], $result[0]);
    }

    /** @test */
    public function it_retrieves_all_projects()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/projects_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/projects_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new RedmineApiService($client);

        $result = $redmine->getProjects();

        $this->assertCount(36, $result);
    }

    /** @test */
    public function it_retrieves_all_trackers()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/trackers.json')),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new RedmineApiService($client);

        $result = $redmine->getTrackers();

        $this->assertCount(23, $result);
        $this->assertTrue(Arr::has($result, ['0.name', '0.id']));
    }

    /** @test */
    public function it_retrieves_all_versions_for_specified_project()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],
                file_get_contents(__DIR__.'/../blobs/project_97_versions.json')),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new RedmineApiService($client);

        $result = $redmine->getVersions(97);
        $this->assertCount(13, $result);
    }

    /** @test */
    public function it_converts_gmt_time_to_local_time()
    {
        $issue = $this->makeFakeRedmineIssue([
            'created_on' => '2017-12-07T06:27:42Z'
        ]);
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'], json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new RedmineApiService($client);
        $result = $redmine->getIssue(322);

        $this->assertEquals('2017-12-07 09:27:42', $result['created_on']);

    }
}
