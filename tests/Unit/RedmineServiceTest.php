<?php

namespace Tests\Unit;

use App\Services\Redmine;
use Carbon\Carbon;
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
}
