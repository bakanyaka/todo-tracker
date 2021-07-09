<?php


namespace App\Services;


use App\Facades\RedmineApi;
use App\Models\Issue;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Service;
use App\Models\Status;
use App\Models\Tracker;
use Carbon\Carbon;

class IssueService
{
    /**
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    public function syncIssueWithRedmine(Issue $issue): Issue
    {
        $data = RedmineApi::getIssue($issue->id);
        $this->fillIssueFromRedmineData($issue, $data);
        $issue->save();
        return $issue;
    }

    public function fillIssueFromRedmineData(Issue $issue, array $data): Issue
    {
        $issue->parent_id = $data['parent_id'];
        $issue->subject = $data['subject'];
        $issue->assigned_to = $data['assigned_to'];
        $issue->assigned_to_id = $data['assigned_to_id'];
        $issue->done_ratio = $data['done_ratio'];
        $issue->created_on = Carbon::create(
            $data['created_on']->year,
            $data['created_on']->month,
            $data['created_on']->day,
            $data['created_on']->hour,
            $data['created_on']->minute,
            $data['created_on']->second,
            $data['created_on']->tz
        );
        $issue->closed_on = $data['closed_on'];
        $issue->updated_on = $data['updated_on'];
        $issue->start_date = $data['start_date'];
        $issue->due_date = $data['due_date'];
        $issue->control = true;

        $priority = Priority::find($data['priority_id']);
        if (!is_null($priority)) {
            $issue->priority_id = $priority->id;
        }

        $status = Status::find($data['status_id']);
        if (!is_null($status)) {
            $issue->status_id = $status->id;
        }

        $project = Project::find($data['project_id']);
        if (!is_null($project)) {
            $issue->project_id = $project->id;
        }

        $tracker = Tracker::find($data['tracker_id']);
        if (!is_null($tracker)) {
            $issue->tracker_id = $tracker->id;
        }

        $service = Service::find($data['service_id']);
        $issue->service_id = $service ? $service->id : null;

        return $issue;
    }
}
