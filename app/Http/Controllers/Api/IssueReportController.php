<?php

namespace App\Http\Controllers\Api;

use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IssueReportController extends Controller
{
    public function index(Request $request)
    {
        $periodDays = $request->period ? $request->period : 7;
        $periodStart = Carbon::now()->subDays($periodDays)->toDateString();

        $issuesCreatedCount = Issue::where('created_on','>',$periodStart)->count();
        $issuesClosedCount = Issue::closed()->where('closed_on','>',$periodStart)->count();
        return response()->json([
           'data' => [
               'created' => $issuesCreatedCount,
               'closed' => $issuesClosedCount
           ]
        ]);
    }
}
