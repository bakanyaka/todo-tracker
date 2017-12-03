<?php

namespace Tests\Unit;

use App\Services\RedmineService;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class RedmineServiceTest extends TestCase
{
    /**
     * @var Generator
     */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create('ru_RU');
    }


    /** @test */
    public function it_retrieves_issue_by_id_using_redmine_api()
    {
        $issue = $this->makeFakeIssue();
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new RedmineService('af38788jkhajghajk7', $client);
        $response = $redmine->getIssue(324);
        $this->assertJson(json_encode($issue), $response);
    }

    protected function makeFakeIssue()
    {
        return [
            'issue' => [
                'id' => $this->faker->randomNumber(2),
                'project_id' => $this->faker->randomNumber(2),
                "subject" => $this->faker->realText(80),
                "priority_id" => 4
            ]
        ];
    }
}
