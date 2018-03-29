<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SyncTimeEntries;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TimeEntryController extends Controller
{
    public function sync()
    {
        if(request('spent_since')) {
            SyncTimeEntries::dispatch(Carbon::parse(request('spent_since')));
        } else {
            SyncTimeEntries::dispatch();
        }

        return response()->json();
    }
}
