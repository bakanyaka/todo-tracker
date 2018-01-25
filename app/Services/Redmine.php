<?php


namespace App\Services;


use App\Exceptions\FailedToRetrieveRedmineDataException;
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
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getIssue($issue_id)
    {
        $issueData = $this->getJsonDataFromRedmine("issues/{$issue_id}.json");
        return $this->parseRedmineIssueData($issueData['issue']);
    }

    /**
     * @param Carbon $dt
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getUpdatedIssues(Carbon $dt)
    {
        $url = "issues.json?updated_on=>={$dt->format('Y-m-d')}&status_id=*";
        $issues = $this->getPaginatedDataFromRedmine($url,'issues');
        return $issues->map([__CLASS__,'parseRedmineIssueData']);
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getProjects()
    {
        $data = $this->getPaginatedDataFromRedmine("project.json",'projects');
        return $data->map([__CLASS__,'parseRedmineProjectData']);
    }

    /**
     * @param string $uri
     * @return array
     * @throws FailedToRetrieveRedmineDataException
     */
    protected function getJsonDataFromRedmine($uri): array
    {
        try {
            $response = $this->client->get($uri);
        } catch (ClientException $exception) {
            throw new FailedToRetrieveRedmineDataException();
        }
        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $url
     * @param string $dataProperty
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    protected function getPaginatedDataFromRedmine($url, $dataProperty)
    {
        $data = $this->getJsonDataFromRedmine($url);
        $total_count = $data['total_count'];
        $limit = $data['limit'];
        $items = collect($data[$dataProperty]);
        for ($offset = $limit; $offset < $total_count; $offset += $limit) {
            $data = $this->getJsonDataFromRedmine("{$url}&offset={$offset}");
            $items = $items->merge($data[$dataProperty]);
        }
        return $items;
    }

    /**
     * @param  array $issue
     * @return array
     */
    static function parseRedmineIssueData($issue)
    {
        $customFields = data_get($issue, 'custom_fields',[]);
        return [
            'id' =>  $issue['id'],
            'status_id' => $issue['status']['id'],
            'priority_id' => $issue['priority']['id'],
            'author' => $issue['author']['name'],
            'assigned_to' => data_get($issue,'assigned_to.name'),
            'subject' => $issue['subject'],
            'description' => $issue['description'],
            'department' => self::getCustomFieldValue($customFields,1),
            'service' => self::getCustomFieldValue($customFields,65),
            'control' => self::getCustomFieldValue($customFields,66),
            'created_on' => Carbon::parse($issue['created_on'])->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse($issue['updated_on'])->timezone('Europe/Moscow'),
            'closed_on' => array_has($issue,'closed_on') ? Carbon::parse($issue['closed_on'])->timezone('Europe/Moscow') : null
        ];
    }
    static function parseRedmineProjectData($project)
    {
        return [
            'id' => $project['id'],
            'name' => $project['name'],
            'identifier' => $project['identifier'],
            'description' => $project['description']
        ];
    }

    static function getCustomFieldValue($customFields,$id)
    {
        return data_get(array_collapse(array_where($customFields, function($value) use ($id) {
            return $value['id'] == $id;
        })),'value');
    }

}