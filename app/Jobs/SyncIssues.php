<?php

namespace App\Jobs;

use App\Facades\RedmineApi;
use App\Models\Issue;
use App\Models\Synchronization;
use App\Services\IssueService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncIssues implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?Carbon $date;
    protected bool $forceUpdateAll;
    protected IssueService $issueService;
    protected int $updatedIssuesCount = 0;

    public function __construct(?Carbon $date = null, bool $forceUpdateAll = false)
    {
        $this->date = $date;
        $this->forceUpdateAll = $forceUpdateAll;
    }

    public function handle(IssueService $issueService)
    {
        $this->issueService = $issueService;
        $issues = RedmineApi::getUpdatedIssues($this->getSyncDate());
        foreach ($issues as $redmineIssue) {
            $this->saveIssue($redmineIssue);
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'issues',
            'updated_items_count' => $this->updatedIssuesCount,
        ]);
    }

    protected function getSyncDate(): Carbon
    {
        if ($this->date !== null) {
            return $this->date;
        }
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        return $lastSync ? $lastSync->completed_at : Carbon::now()->subMonth();
    }

    protected function saveIssue(array $redmineIssue): Issue
    {
        $issue = Issue::setEagerLoads([])->withoutGlobalScopes()->firstOrNew(['id' => $redmineIssue['id']]);
        // Only update issue if it was updated in redmine
        if ($this->forceUpdateAll || $issue->updated_on === null || $issue->updated_on->lt($redmineIssue['updated_on'])) {
            $this->issueService->fillIssueFromRedmineData($issue, $redmineIssue );
            $issue->save();
            $this->updatedIssuesCount++;
        }
        return $issue;
    }

}
