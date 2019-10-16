<?php

namespace App\Jobs;

use App\Facades\Redmine;
use App\Models\Issue;
use App\Models\Synchronization;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncIssues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $date;
    /**
     * @var bool
     */
    protected $forceUpdateAll;

    /**
     * Create a new job instance.
     *
     * @param  null  $date
     * @param  bool  $forceUpdateAll
     */
    public function __construct($date = null, $forceUpdateAll = false)
    {
        $this->date = $date;
        $this->forceUpdateAll = $forceUpdateAll;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->date === null) {
            $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
            $lastSyncDate = $lastSync ? $lastSync->completed_at : Carbon::now()->subMonth();
        } else {
            $lastSyncDate = $this->date;
        }
        $issues = Redmine::getUpdatedIssues($lastSyncDate);
        $updatedIssuesCount = 0;
        foreach ($issues as $redmineIssue) {
            $issue = Issue::setEagerLoads([])->withoutGlobalScopes()->firstOrNew(['id' => $redmineIssue['id']]);
            // Only update issue if it was updated in redmine
            if ($this->forceUpdateAll || $issue->updated_on === null || $issue->updated_on->lt($redmineIssue['updated_on']) ) {
                $issue->updateFromRedmineIssue($redmineIssue);
                $issue->save();
                $updatedIssuesCount++;
            }
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'issues',
            'updated_items_count' => $updatedIssuesCount
        ]);
    }
}
