<?php


namespace App\Services;


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
        return json_decode($response->getBody(),true);
    }
}