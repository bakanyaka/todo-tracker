<?php

namespace App\Jobs;

use App\Facades\Redmine;
use App\Models\Synchronization;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncTimeEntries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Carbon
     */
    public $date;

    /**
     * Create a new job instance.
     *
     * @param Carbon $date
     */
    public function __construct(Carbon $date = null)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \App\Exceptions\FailedToRetrieveRedmineDataException
     */
    public function handle()
    {
        if ($this->date === null) {
            $spentOn = Carbon::now();
        } else {
            $spentOn = $this->date;
        }

        $timeEntriesRM = Redmine::getTimeEntries($spentOn);
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
