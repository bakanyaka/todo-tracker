<?php

namespace App\Jobs;

use App\Facades\RedmineApi;
use App\Models\Synchronization;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTimeEntries implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?Carbon $date;

    public function __construct(?Carbon $date = null)
    {
        $this->date = $date;
    }

    public function handle()
    {
        if ($this->date === null) {
            $spentOn = Carbon::now();
        } else {
            $spentOn = $this->date;
        }

        $timeEntriesRM = RedmineApi::getTimeEntries($spentOn);
        foreach ($timeEntriesRM as $timeEntryRM) {
            $timeEntry = TimeEntry::firstOrNew(['id' => $timeEntryRM['id']]);
            $timeEntry->assignee_id = $timeEntryRM['assignee_id'];
            $timeEntry->project_id = $timeEntryRM['project_id'];
            $timeEntry->issue_id = $timeEntryRM['issue_id'];
            $timeEntry->hours = $timeEntryRM['hours'];
            $timeEntry->comments = $timeEntryRM['comments'];
            $timeEntry->spent_on = $timeEntryRM['spent_on'];
            $timeEntry->save();
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'time_entries',
        ]);
    }
}
