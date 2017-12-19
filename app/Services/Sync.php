<?php


namespace App\Services;


use App\Facades\Redmine;
use App\Models\Issue;
use App\Models\Priority;
use App\Models\Service;
use App\Models\Synchronization;
use Carbon\Carbon;

class Sync
{

    public function synchronize()
    {
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        $lastSyncDate = $lastSync ? $lastSync->completed_at : Carbon::now()->subWeek();
        try {
            $issues = Redmine::getUpdatedIssues($lastSyncDate);
        } catch (\Exception $exception)
        {
            //TODO: Error Reporting
            return;
        }
        foreach ($issues as $redmineIssue) {
            $issue = Issue::firstOrNew(['id' => $redmineIssue['id']]);
            $issue->updateFromRedmineIssue($redmineIssue);
            $issue->save();
        }
        Synchronization::create([
            'completed_at' => Carbon::now()
        ]);
    }
}