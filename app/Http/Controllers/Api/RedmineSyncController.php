<?php

namespace App\Http\Controllers\Api;

use App\Models\Synchronization;
use App\Http\Resources\Synchronization as SynchronizationResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RedmineSyncController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Synchronization  $synchronization
     * @return \Illuminate\Http\Response
     */
    public function edit(Synchronization $synchronization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Synchronization  $synchronization
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Synchronization $synchronization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Synchronization  $synchronization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Synchronization $synchronization)
    {
        //
    }
}
