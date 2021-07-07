<?php

namespace App\Http\Controllers\Api;

use App\Facades\RedmineApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tracker as TrackerResource;
use App\Models\Synchronization;
use App\Models\Tracker;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrackerController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        return TrackerResource::collection(Tracker::all());
    }

    public function sync()
    {
        $redmineTrackers = RedmineApi::getTrackers();
        foreach ($redmineTrackers as $redmineTracker) {
            $project = Tracker::firstOrNew(['id' => $redmineTracker['id']]);
            $project->name = $redmineTracker['name'];
            $project->save();
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'trackers',
        ]);
    }

}
