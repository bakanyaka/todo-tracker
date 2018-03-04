<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FailedToRetrieveRedmineDataException;
use App\Filters\IssueFilters;
use App\Http\Resources\IssueCollection;
use App\Jobs\SyncIssues;
use App\Models\Issue;
use App\User;
use Carbon\Carbon;
use function GuzzleHttp\Promise\all;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IssueFilters $filters
     * @return IssueCollection
     */
    public function index(IssueFilters $filters)
    {
        $issues = Issue::filter($filters)->get();
        switch (request()->overdue) {
            case 'yes':
                $issues = $issues->filter(function(Issue $issue) {
                    return $issue->due_date !== null && $issue->time_left < 0;
                });
                break;
            case 'no':
                $issues = $issues->filter(function(Issue $issue) {
                    return $issue->due_date !== null && $issue->time_left >= 0;
                });
                break;
            case 'soon':
                $issues = $issues->filter(function (Issue $issue) {
                    if ($issue->due_date === null) {
                        return false;
                    }
                    return $issue->due_date->toDateString() === now()->toDateString() && $issue->percent_of_time_left < 30 && $issue->percent_of_time_left > 0;
                });
                break;
        }

        $issues = $issues->sort([Issue::class, 'defaultSort'])->values();//->paginate(5);
        return new IssueCollection($issues);
    }

    public function sync()
    {
        if(request('updated_since')) {
            SyncIssues::dispatch(Carbon::parse(request('updated_since')));
        } else {
            SyncIssues::dispatch();
        }

        return response()->json();
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function store(Request $request)
    {
        $request->validate(['issue_id' => 'required|int']);
        $issue = Issue::firstOrNew(['id' => $request->issue_id]);
        try {
            $issue->updateFromRedmine();
        } catch (FailedToRetrieveRedmineDataException $exception) {
            abort(404);
        }
        $issue->save();
        $issue->track(auth()->user());
        return response()->json([],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function show(Issue $issue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function edit(Issue $issue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Issue $issue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function destroy(Issue $issue)
    {
        $issue->untrack(auth()->user());
        return response()->json([]);
    }
}
