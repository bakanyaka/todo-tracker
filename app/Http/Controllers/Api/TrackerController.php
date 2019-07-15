<?php

namespace App\Http\Controllers\Api;

use App\Facades\Redmine;
use App\Models\Synchronization;
use App\Models\Tracker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\Tracker as TrackerResource;
use App\Http\Controllers\Controller;

class TrackerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return TrackerResource::collection(Tracker::all());
    }

    /**
     * Sync trackers with redmine
     *
     * @return \Illuminate\Http\Response
     */
    public function sync()
    {
        $redmineTrackers = Redmine::getTrackers();
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
