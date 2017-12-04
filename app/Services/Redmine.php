<?php


namespace App\Services;


use Carbon\Carbon;
use GuzzleHttp\Client;

class Redmine
{
    protected $redmineUrl;
    protected $token;
    protected $client;

    /**
     * RedmineService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getIssue($issue_id)
    {
        $response = $this->client->get("issues/{$issue_id}.json");
        return $this->parseRedmineInfo(json_decode($response->getBody(),true));
    }

    /**
     * @param  array $issue
     * @return array
     */
    private function parseRedmineInfo($issue)
    {
        $issueData = $issue['issue'];
        return [
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
        ];
    }
}