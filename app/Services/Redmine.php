<?php


namespace App\Services;


use App\Exceptions\FailedToRetrieveRedmineDataException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;

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
        return $issues->map([$this,'parseRedmineIssueData']);
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getProjects()
    {
        $data = $this->getPaginatedDataFromRedmine("projects.json",'projects');
        return $data->map([$this,'parseRedmineProjectData']);
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getTrackers()
    {
        $data = $this->getPaginatedDataFromRedmine("trackers.json",'trackers');
        return $data->map([$this,'parseRedmineTrackerData']);
    }



    /**
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getUsers()
    {
        $data = $this->getPaginatedDataFromRedmine('users.json','users');
        return $data->map([$this, 'parseRedmineUserData']);
    }

    /**
     * @param Carbon $dt
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getTimeEntries(Carbon $dt)
    {
        $url = "time_entries.json?spent_on=>={$dt->format('Y-m-d')}";
        $data = $this->getPaginatedDataFromRedmine($url,'time_entries');
        return $data->map([$this, 'parseRedmineTimeEntryData']);
    }

    /**
     * @param int $projectId
     * @return \Illuminate\Support\Collection
     * @throws FailedToRetrieveRedmineDataException
     */
    public function getVersions(int $projectId)
    {
        $data = $this->getPaginatedDataFromRedmine( "/projects/{$projectId}/versions.json",'versions');
        return $data->map([$this, 'parseRedmineVersionData']);
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
            throw new FailedToRetrieveRedmineDataException($exception->getMessage());
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
        if(strpos($url,'?') === false) {
            $url .= '?';
        }
        $data = $this->getJsonDataFromRedmine($url);
        $items = collect($data[$dataProperty]);
        if (Arr::has($data, ['total_count', 'limit'])) {
            $total_count = $data['total_count'];
            $limit = $data['limit'];
            for ($offset = $limit; $offset < $total_count; $offset += $limit) {
                $data = $this->getJsonDataFromRedmine("{$url}&offset={$offset}");
                $items = $items->merge($data[$dataProperty]);
            }
        }
        return $items;
    }

    /**
     * @param  array $issue
     * @return array
     */
    public function parseRedmineIssueData($issue)
    {
        $customFields = data_get($issue, 'custom_fields',[]);
        return [
            'id' =>  $issue['id'],
            'project_id' => $issue['project']['id'],
            'status_id' => $issue['status']['id'],
            'tracker_id' => $issue['tracker']['id'],
            'priority_id' => $issue['priority']['id'],
            'author' => $issue['author']['name'],
            'author_id' => $issue['author']['id'],
            'assigned_to' => data_get($issue,'assigned_to.name'),
            'assigned_to_id' => data_get($issue,'assigned_to.id'),
            'subject' => $issue['subject'],
            'description' => $issue['description'],
            'service_id' => $this->getCustomFieldValue($customFields,[80, 81]),
            'start_date' => Carbon::parse($issue['start_date'])->timezone('Europe/Moscow'),
            'created_on' => Carbon::parse($issue['created_on'])->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse($issue['updated_on'])->timezone('Europe/Moscow'),
            'closed_on' => array_get($issue,'closed_on') ? Carbon::parse($issue['closed_on'])->timezone('Europe/Moscow') : null
        ];
    }

    public function parseRedmineTrackerData($tracker)
    {
        return [
            'id' => $tracker['id'],
            'name' => $tracker['name'],
        ];
    }

    public function parseRedmineProjectData($project)
    {
        return [
            'id' => $project['id'],
            'name' => $project['name'],
            'identifier' => $project['identifier'],
            'description' => $project['description'],
            'parent_id' => data_get($project,'parent.id')
        ];
    }

    public function parseRedmineUserData($user)
    {
        return [
            'id' => $user['id'],
            'login' => $user['login'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'mail' => $user['mail'],
        ];
    }

    public function parseRedmineTimeEntryData($timeEntry)
    {
        return [
            'id' => $timeEntry['id'],
            'assignee_id' => array_get($timeEntry, 'user.id'),
            'project_id' => array_get($timeEntry, 'project.id'),
            'issue_id' => array_get($timeEntry, 'issue.id'),
            'hours' => $timeEntry['hours'],
            'comments' => $timeEntry['comments'],
            'spent_on' => Carbon::parse($timeEntry['spent_on'])->timezone('Europe/Moscow'),
        ];
    }

    public function parseRedmineVersionData($version)
    {
        $customFields = data_get($version, 'custom_fields',[]);
        return [
            'id' => $version['id'],
            'project_id' => array_get($version, 'project.id'),
            'name' => $version['name'],
            'custom_fields' => $version['custom_fields'],
        ];
    }

    public function getCustomFieldValue($customFields,$fieldId)
    {
        $ids = Arr::wrap($fieldId);
        foreach ($ids as $id) {
            $value = Arr::get(Arr::first($customFields, function ($value) use ($id) {
                return $value['id'] == $id;
            }), 'value');
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

}
