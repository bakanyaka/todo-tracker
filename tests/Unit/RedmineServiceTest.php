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
            new Response(200, ['content-type' => 'application/json; charset=utf8'],json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new Redmine($client);
        $result = $redmine->getIssue(324);
        $this->assertEquals([
            'id' =>  $issueData['id'],
            'project_id' => $issueData['project']['id'],
            'status_id' => $issueData['status']['id'],
            'priority_id' => $issueData['priority']['id'],
            'author' => $issueData['author']['name'],
            'assigned_to' => $issueData['assigned_to']['name'],
            'subject' => $issueData['subject'],
            'description' => $issueData['description'],
            'department' => $issueData['custom_fields'][0]['value'],
            'service' => $issueData['custom_fields'][1]['value'],
            'control' => $issueData['custom_fields'][2]['value'],
            'created_on' => Carbon::parse($issueData['created_on']),
            'updated_on' => Carbon::parse($issueData['updated_on']),
            'closed_on' => Carbon::parse($issueData['closed_on']),
        ],$result);
    }

    /** @test */
    public function it_retrieves_all_issues_modified_since_given_date()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],file_get_contents(__DIR__.'/../blobs/issues_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'],file_get_contents(__DIR__.'/../blobs/issues_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new Redmine($client);

        $result = $redmine->getUpdatedIssues(Carbon::parse('2017-12-13'));

        $this->assertCount(49,$result);
    }

    /** @test */
    public function it_retrieves_all_projects()
    {
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],file_get_contents(__DIR__.'/../blobs/projects_page_1.json')),
            new Response(200, ['content-type' => 'application/json; charset=utf8'],file_get_contents(__DIR__.'/../blobs/projects_page_2.json'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $redmine = new Redmine($client);

        $result = $redmine->getProjects();

        $this->assertCount(36, $result);
    }


    /** @test */
    public function it_converts_gmt_time_to_local_time() {
        $issue = $this->makeFakeRedmineIssue([
            'created_on' => '2017-12-07T06:27:42Z'
        ]);
        $issueData = $issue['issue'];
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new Redmine($client);
        $result = $redmine->getIssue(322);

        $this->assertEquals('2017-12-07 09:27:42',$result['created_on']);

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
            ],$attributes)
        ];
        return $issue;
    }
}
