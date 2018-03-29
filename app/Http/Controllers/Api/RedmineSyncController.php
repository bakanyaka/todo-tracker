<?php

namespace App\Http\Controllers\Api;

use App\Models\Synchronization;
use App\Http\Resources\Synchronization as SynchronizationResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RedmineSyncController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return SynchronizationResource
     */
    public function index()
    {
        $issuesLastSync = Synchronization::whereNotNull('completed_at')
            ->where('type','issues')
            ->orderByDesc('completed_at')
            ->first();

        $timeEntriesLastSync = Synchronization::whereNotNull('completed_at')
            ->where('type','time_entries')
            ->orderByDesc('completed_at')
            ->first();

        $AssigneesLastSync = Synchronization::whereNotNull('completed_at')
            ->where('type','assignees')
            ->orderByDesc('completed_at')
            ->first();

        $ProjectsLastSync = Synchronization::whereNotNull('completed_at')
            ->where('type','projects')
            ->orderByDesc('completed_at')
            ->first();

        return response()->json([
            'data' => [
                'issues' => $issuesLastSync ? new SynchronizationResource($issuesLastSync) : null,
                'time_entries' => $timeEntriesLastSync ? new SynchronizationResource($timeEntriesLastSync) : null,
                'assignees' => $AssigneesLastSync ? new SynchronizationResource($AssigneesLastSync) : null,
                'projects' => $ProjectsLastSync ? new SynchronizationResource($ProjectsLastSync) : null,
            ]
        ]);
    }



}
