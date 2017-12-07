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
        $customFields = data_get($issueData, 'custom_fields',[]);
        $department = $this->getCustomFieldValue($customFields,1);
        $service = $this->getCustomFieldValue($customFields,65);

        return [
            'id' =>  $issueData['id'],
            'status' => $issueData['status']['id'],
            'priority' => $issueData['priority']['name'],
            'author' => $issueData['author']['name'],
            'assignedTo' => data_get($issueData,'assigned_to.name'),
            'subject' => $issueData['subject'],
            'description' => $issueData['description'],
            'department' => $department,
            'service' => $service,
            'created_on' => Carbon::parse($issueData['created_on'])->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse($issueData['updated_on'])->timezone('Europe/Moscow'),
            'closed_on' => array_has($issueData,'closed_on') ? Carbon::parse($issueData['closed_on'])->timezone('Europe/Moscow') : null
        ];
    }

    private function getCustomFieldValue($customFields,$id)
    {
        return data_get(array_collapse(array_where($customFields, function($value) use ($id) {
            return $value['id'] == $id;
        })),'value');
    }
}