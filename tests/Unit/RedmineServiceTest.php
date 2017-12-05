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
        $response = $redmine->getIssue(324);
        $this->assertEquals([
            'id' =>  $issueData['id'],
            'status' => $issueData['status']['id'],
            'priority' => $issueData['priority']['name'],
            'author' => $issueData['author']['name'],
            'assignedTo' => $issueData['assigned_to']['name'],
            'subject' => $issueData['subject'],
            'description' => $issueData['description'],
            'department' => $issueData['custom_fields'][0]['value'],
            'service' => $issueData['custom_fields'][1]['value'],
            'created_on' => Carbon::parse($issueData['created_on']),
            'updated_on' => Carbon::parse($issueData['updated_on']),
        ],$response);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function makeFakeRedmineIssue($attributes = [])
    {
        $issue = array_merge([
            'issue' => [
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
                    ]
                ],
                'created_on' => $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z'),
                'updated_on' => $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z'),
            ]
        ], $attributes);
        return $issue;
    }
}
