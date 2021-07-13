<?php


namespace App\Services;


use App\Exceptions\FailedToRetrieveRedmineDataException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RedmineApiService
{

    public function __construct(protected Client $client)
    {
    }

    /**
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getIssue($issue_id): array
    {
        $issueData = $this->getJsonDataFromRedmine("issues/{$issue_id}.json");
        return $this->parseRedmineIssueData($issueData['issue']);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    public function getIssueCategory(int $id): array
    {
        $data = $this->getJsonDataFromRedmine("issue_categories/{$id}.json");
        return $this->parseRedmineCategoryData($data['issue_category']);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    public function getUpdatedIssues(Carbon $dt): Collection
    {
        $url = "issues.json?updated_on=>={$dt->format('Y-m-d')}&status_id=*";
        $issues = $this->getPaginatedDataFromRedmine($url, 'issues');
        return $issues->map([$this, 'parseRedmineIssueData']);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    public function getProjects(): Collection
    {
        $data = $this->getPaginatedDataFromRedmine("projects.json", 'projects');
        return $data->map([$this, 'parseRedmineProjectData']);
    }


    /**
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTrackers(): Collection
    {
        $data = $this->getPaginatedDataFromRedmine("trackers.json", 'trackers');
        return $data->map([$this, 'parseRedmineTrackerData']);
    }


    /**
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUsers(): Collection
    {
        $data = $this->getPaginatedDataFromRedmine('users.json', 'users');
        return $data->map([$this, 'parseRedmineUserData']);
    }


    /**
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTimeEntries(Carbon $dt): Collection
    {
        $url = "time_entries.json?spent_on=>={$dt->format('Y-m-d')}";
        $data = $this->getPaginatedDataFromRedmine($url, 'time_entries');
        return $data->map([$this, 'parseRedmineTimeEntryData']);
    }


    /**
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getVersions(int $projectId): Collection
    {
        $data = $this->getPaginatedDataFromRedmine("/projects/{$projectId}/versions.json", 'versions');
        return $data->map([$this, 'parseRedmineVersionData']);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    protected function getJsonDataFromRedmine(string $uri): array
    {
        try {
            $response = $this->client->get($uri);
        } catch (ClientException $exception) {
            throw new FailedToRetrieveRedmineDataException($exception->getMessage());
        }
        return json_decode($response->getBody(), true);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    protected function getPaginatedDataFromRedmine(string $url, string $dataProperty): Collection
    {
        if (!str_contains($url, '?')) {
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

    public function parseRedmineIssueData(array $issue): array
    {
        $customFields = data_get($issue, 'custom_fields', []);
        return [
            'id' => $issue['id'],
            'parent_id' => data_get($issue, 'parent.id'),
            'project_id' => $issue['project']['id'],
            'status_id' => $issue['status']['id'],
            'tracker_id' => $issue['tracker']['id'],
            'priority_id' => $issue['priority']['id'],
            'author' => $issue['author']['name'],
            'author_id' => $issue['author']['id'],
            'assigned_to' => data_get($issue, 'assigned_to.name'),
            'assigned_to_id' => data_get($issue, 'assigned_to.id'),
            'category_id' => data_get($issue, 'category.id'),
            'subject' => $issue['subject'],
            'description' => $issue['description'],
            'service_id' => $this->getCustomFieldValue($customFields, [80, 81]),
            'done_ratio' => $issue['done_ratio'],
            'start_date' => $issue['start_date'] ? Carbon::parse($issue['start_date'])->timezone('Europe/Moscow') : null,
            'due_date' => $issue['due_date'] ? Carbon::parse($issue['due_date'])->timezone('Europe/Moscow') : null,
            'created_on' => Carbon::parse($issue['created_on'])->timezone('Europe/Moscow'),
            'updated_on' => Carbon::parse($issue['updated_on'])->timezone('Europe/Moscow'),
            'closed_on' => array_get($issue, 'closed_on') ? Carbon::parse($issue['closed_on'])
                ->timezone('Europe/Moscow') : null,
        ];
    }

    public function parseRedmineTrackerData($tracker): array
    {
        return [
            'id' => $tracker['id'],
            'name' => $tracker['name'],
        ];
    }

    public function parseRedmineProjectData($project): array
    {
        return [
            'id' => $project['id'],
            'name' => $project['name'],
            'identifier' => $project['identifier'],
            'description' => $project['description'],
            'parent_id' => data_get($project, 'parent.id'),
        ];
    }

    public function parseRedmineUserData($user): array
    {
        return [
            'id' => $user['id'],
            'login' => $user['login'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'mail' => $user['mail'],
        ];
    }

    public function parseRedmineTimeEntryData($timeEntry): array
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

    public function parseRedmineVersionData($version): array
    {
        return [
            'id' => $version['id'],
            'project_id' => array_get($version, 'project.id'),
            'name' => $version['name'],
            'custom_fields' => array_get($version, 'custom_fields', []),
        ];
    }

    protected function parseRedmineCategoryData(array $category): array
    {
        return [
            'id' => $category['id'],
            'name' => $category['name'],
        ];
    }

    public function getCustomFieldValue($customFields, $fieldId)
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
