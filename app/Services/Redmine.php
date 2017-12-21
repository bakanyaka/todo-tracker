<?php


namespace App\Services;


use App\Exceptions\FailedToRetrieveRedmineIssueException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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

    /**
     * @param $issue_id
     * @return array
     * @throws FailedToRetrieveRedmineIssueException
     */
    public function getIssue($issue_id)
    {
        $issueData = $this->getJsonDataFromRedmine("issues/{$issue_id}.json");
        return $this->parseRedmineInfo($issueData['issue']);
    }

    /**
     * @param  array $issue
     * @return array
     */
    private function parseRedmineInfo($issue)
    {
        $customFields = data_get($issue, 'custom_fields',[]);
        return [
            'id' =>  $issue['id'],
            'status' => $issue['status']['id'],
            'priority_id' => $issue['priority']['id'],
            'author' => $issue['author']['name'],
            'assigned_to' => data_get($issue,'assigned_to.name'),
            'subject' => $issue['subject'],
            'description' => $issue['description'],
            'department' => $this->getCustomFieldValue($customFields,1),
            'service' => $this->getCustomFieldValue($customFields,65),
            'control' => $this->getCustomFieldValue($customFields,66),
            'created_on' => Carbon::parse($issue['created_on'])->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse($issue['updated_on'])->timezone('Europe/Moscow'),
            'closed_on' => array_has($issue,'closed_on') ? Carbon::parse($issue['closed_on'])->timezone('Europe/Moscow') : null
        ];
    }

    private function getCustomFieldValue($customFields,$id)
    {
        return data_get(array_collapse(array_where($customFields, function($value) use ($id) {
            return $value['id'] == $id;
        })),'value');
    }

    /**
     * @param Carbon $dt
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineIssueException
     */
    public function getUpdatedIssues(Carbon $dt)
    {
        $data = $this->getJsonDataFromRedmine("issues.json?updated_on=>={$dt->format('Y-m-d')}&status_id=*");
        $total_count = $data['total_count'];
        $limit = $data['limit'];
        $issues = collect($data['issues']);
        for ($offset = $limit;$offset < $total_count; $offset += $limit) {
            $data = $this->getJsonDataFromRedmine("issues.json?updated_on=>={$dt->format('Y-m-d')}&offset={$offset}&status_id=*");
            $issues = $issues->merge($data['issues']);
        }
        return $issues->map(function ($issue) {
            return $this->parseRedmineInfo($issue);
        });
    }

    /**
     * @param string $uri
     * @return array
     * @throws FailedToRetrieveRedmineIssueException
     */
    protected function getJsonDataFromRedmine($uri): array
    {
        try {
            $response = $this->client->get($uri);
        } catch (ClientException $exception) {
            throw new FailedToRetrieveRedmineIssueException();
        }
        return json_decode($response->getBody(), true);
    }
}