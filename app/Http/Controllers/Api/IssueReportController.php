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
        $periodEnd = Carbon::now()->toDateString();

        $zeroDates = collect();
        for ($d = $periodDays; $d > 0; $d--) {
            $date = now()->subDays($d)->toDateString();
            $zeroDates[$date] = [
                'x' => $date,
                'y' => 0
            ];
        }

        $issuesCreated = Issue::where('created_on', '>', $periodStart)
            ->where('created_on', '<', $periodEnd)
            ->selectRaw('Date(created_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map(function ($item) {
                return [
                    'x' => $item->date,
                    'y' => (int)$item->count
                ];
            })->keyBy('x');

        $issuesClosed = Issue::where('closed_on', '>', $periodStart)
            ->where('closed_on', '<', $periodEnd)
            ->selectRaw('Date(closed_on) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')->get()
            ->map(function ($item) {
                return [
                    'x' => $item->date,
                    'y' => (int)$item->count
                ];
            })->keyBy('x');

        $issuesCreated = $zeroDates->merge($issuesCreated)->values();
        $issuesClosed = $zeroDates->merge($issuesClosed)->values();

        return response()->json([
           'data' => [
               'created' => [
                   'total' => $issuesCreated->sum('y'),
                   'data' => $issuesCreated
               ],
               'closed' => [
                   'total' => $issuesClosed->sum('y'),
                   'data' => $issuesClosed
               ]
           ]
        ]);
    }
}
