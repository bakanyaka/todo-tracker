<?php


namespace App\Services;


use GuzzleHttp\Client;

class RedmineService
{
    protected $redmineUrl;
    protected $token;
    protected $client;

    /**
     * RedmineService constructor.
     * @param string $token
     * @param Client $client
     */
    public function __construct(Client $client, $token = '')
    {
        $this->token = $token;
        $this->client = $client;
    }

    public function getIssue($issue_id)
    {
        $response = $this->client->get("issues/{$issue_id}.json");
        return json_decode($response->getBody());
    }
}