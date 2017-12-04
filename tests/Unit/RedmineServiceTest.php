<?php

namespace Tests\Unit;

use App\Services\Redmine;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Tests\Traits\MakesFakeIssues;

class RedmineServiceTest extends TestCase
{
    use MakesFakeIssues;

    /** @test */
    public function it_retrieves_issue_by_id_using_redmine_api()
    {
        $issue = $this->makeFakeIssue();
        $mock = new MockHandler([
            new Response(200, ['content-type' => 'application/json; charset=utf8'],json_encode($issue))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $redmine = new Redmine($client);
        $response = $redmine->getIssue(324);
        $this->assertJson(json_encode($issue), $response);
    }

}
