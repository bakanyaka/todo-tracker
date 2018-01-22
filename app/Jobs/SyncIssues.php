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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        $lastSyncDate = $lastSync ? $lastSync->completed_at : Carbon::now()->subMonth();
        $issues = Redmine::getUpdatedIssues($lastSyncDate);
        foreach ($issues as $redmineIssue) {
            $issue = Issue::firstOrNew(['id' => $redmineIssue['id']]);
            // Only update issue if it was updated in redmine
            if ($issue->updated_on === null || $issue->updated_on->lt($redmineIssue['updated_on']) ) {
                $issue->updateFromRedmineIssue($redmineIssue);
                $issue->save();
            }
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'updated_issues_count' => $issues->count()
        ]);
    }
}
