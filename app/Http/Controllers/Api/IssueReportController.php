<?php

namespace App\Http\Controllers\Api;

use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IssueReportController extends Controller
{
    public function index(Request $request)
    {
        $periodDays = $request->period ? $request->period : 7;
        $periodStart = Carbon::now()->subDays($periodDays)->toDateString();
        $issuesCreated = Issue::where('created_on', '>', $periodStart)
            ->selectRaw('Date(created_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get();
        for ($d =1; $d <= $periodDays; $d++) {
            $dates[] = Carbon::now()->subDays($d)->toDateString();
        }
        //$collection->firstWhere('age', '>=', 18);
        //$keyed = $collection->keyBy('product_id');
        //mapToGroups()
        //mapWithKeys()
        dd($issuesCreated);



/*        $issuesCreatedCount = Issue::where('created_on','>',$periodStart)->count();
        $issuesClosedCount = Issue::closed()->where('closed_on','>',$periodStart)->count();
        return response()->json([
           'data' => [
               'created' => $issuesCreatedCount,
               'closed' => $issuesClosedCount
           ]
        ]);*/
    }
}
