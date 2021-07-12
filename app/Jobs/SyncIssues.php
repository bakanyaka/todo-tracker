<?php

namespace App\Jobs;

use App\Facades\RedmineApi;
use App\Models\Synchronization;
use App\Services\IssueSynchronizationService;
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

    protected IssueSynchronizationService $issueService;
    protected int $updatedIssuesCount = 0;

    public function __construct(public ?Carbon $syncFromDate = null, protected bool $forceUpdateAll = false)
    {
    }

    /**
     * @throws \Throwable
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    public function handle(IssueSynchronizationService $issueService)
    {
        $this->issueService = $issueService;
        $redmineIssues = RedmineApi::getUpdatedIssues($this->getSyncDate())->sortBy('parent_id');
        foreach ($redmineIssues as $redmineIssue) {
            $this->issueService->syncIssueWithRedmineData($redmineIssue, $this->forceUpdateAll);
            $this->updatedIssuesCount++;
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'issues',
            'updated_items_count' => $this->updatedIssuesCount,
        ]);
    }

    protected function getSyncDate(): Carbon
    {
        if ($this->syncFromDate !== null) {
            return $this->syncFromDate;
        }
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        return $lastSync ? $lastSync->completed_at : Carbon::now()->subMonth();
    }

}
