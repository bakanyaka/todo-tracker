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
    public function show()
    {
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        return $lastSync ? new SynchronizationResource($lastSync): response()->json([]);
    }



}
