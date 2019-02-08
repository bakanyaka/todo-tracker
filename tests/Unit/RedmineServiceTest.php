<?php

namespace Tests\Unit;

use App\Services\Redmine;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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

        $redmine = new Redmine($client);
        $result = $redmine->getIssue(324);
        $this->assertEquals([
            'id' => $issueData['id'],
            'project_id' => $issueData['project']['id'],
            'tracker_id' => $issueData['tracker']['id'],
            'status_id' => $issueData['status']['id'],
            'priority_id' => $issueData['priority']['id'],
            'author' => $issueData['author']['name'],
            'assigned_to' => $issueData['assigned_to']['name'],
            'subject' => $issueData['subject'],
            'description' => $issueData['description'],
            'department' => $issueData['custom_fields'][0]['value'],
            'service' => $issueData['custom_fields'][1]['value'],
            'control' => $issueData['custom_fields'][2]['value'],
            'start_date' => Carbon::parse($issueData['start_date']),
            'created_on' => Carbon::parse($issueData['created_on']),
            'updated_on' => Carbon::parse($issueData['updated_on']),
            'closed_on' => Carbon::parse($issueData['closed_on']),
            'assigned_to_id' => $issueData['assigned_to']['id'],
        ], $result);
    }

    /**
     * @param array $attributes
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
                'subject' => $this->faker->name . ' : ' . $this->faker->realText(60),
                'description' => $this->faker->realText(),
                'start_date' => $this->faker->dateTimeThisMonth()->format('Y-m-d'),
                'done_ratio' => $this->faker->numberBetween(0, 100),
                'custom_fields' => [
                    [
                        'id' => 1,
                        'name' => 'Подразделение',
                        'value' => '115 Управление информационных систем'
                    ],
                    [
                        'id' => 65,
                        'name' => 'Сервис',
                        'value' => 'Организация рабочих мест пользователей'
                    ],
                    [
                        'id' => 66,
                        'name' => 'Контроль',
                        'value' => 1
                    ]
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
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/issues_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/issues_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new Redmine($client);

        $result = $redmine->getUpdatedIssues(Carbon::parse('2017-12-13'));

        $this->assertCount(49, $result);
        $this->assertArraySubset([
            "id" => 40142,
            "project_id" => 102,
            "status_id" => 1,
            "priority_id" => 7,
            "author" => "Николай Федоровых",
            "assigned_to" => "Николай Федоровых",
            "assigned_to_id" => 115,
            "subject" => 'Ошибка "{Обработка.СА_УправлениеСканАрхивом.Форма.Форма.Форма(2605)}: Значение не является значением объектного типа (Получить)" (Якина Анна)',
            "description" => "",
            "department" => "123  Управление по бухгалтерскому учету и отчетности",
            "service" => null,
            "control" => "0",
            'created_on' => Carbon::parse('2017-12-18 11:38:32'),
            'updated_on' => Carbon::parse('2017-12-18 11:38:32'),
            'closed_on' => null
        ], $result[0]);
    }

    /** @test */
    public function it_retrieves_all_time_entries_since_given_date()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/time_entries_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/time_entries_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new Redmine($client);

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
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/users_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/users_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new Redmine($client);

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
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/projects_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'], file_get_contents(__DIR__ . '/../blobs/projects_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new Redmine($client);

        $result = $redmine->getProjects();

        $this->assertCount(36, $result);
    }

    /** @test */
    public function it_converts_gmt_time_to_local_time()
    {
        $issue = $this->makeFakeRedmineIssue([
            'created_on' => '2017-12-07T06:27:42Z'
        ]);
        $issueData = $issue['issue'];
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'], json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new Redmine($client);
        $result = $redmine->getIssue(322);

        $this->assertEquals('2017-12-07 09:27:42', $result['created_on']);

    }
}
