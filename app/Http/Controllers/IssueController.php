<?php

namespace App\Http\Controllers;

use App\Exceptions\FailedToRetrieveRedmineIssueException;
use App\Facades\Sync;
use App\Jobs\SyncIssues;
use App\Models\Issue;
use App\Models\Synchronization;
use App\User;
use Exception;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request('user') === null) {
            $issues = auth()->user()->issues();
        } elseif (request('user') === 'all') {
            $issues = Issue::has('users');
        } else {
            $user = User::whereUsername(request('user'))->firstOrFail();
            $issues = $user->issues;
        }
        if(request('only_open') !== 'false'){
            $issues->open();
        }
        $issues = $issues->get()->sort([Issue::class, 'defaultSort']);
        $lastSync = Synchronization::whereNotNull('completed_at')->orderByDesc('completed_at')->first();
        $lastSync = $lastSync ? $lastSync->completed_at->diffForHumans() : 'никогда';
        return view('issues.index', ['issues' => $issues, 'lastSync' => $lastSync]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {

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
        } catch (FailedToRetrieveRedmineIssueException $exception) {
            abort(404);
        }
        $issue->save();
        $issue->track(auth()->user());
        return redirect(route('issues'));
    }


    public function updateAll()
    {
        try {
            Issue::open()->each(function ($issue) {
                $issue->updateFromRedmine()->save();
            });
        } catch (FailedToRetrieveRedmineIssueException $exception) {
            // TODO: Log error
        }
        return redirect(route('issues'));
    }

    public function sync()
    {
        SyncIssues::dispatch();
        return redirect(route('issues'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Issue $issue
     * @return \Illuminate\Http\Response
     */
    public function destroy(Issue $issue)
    {
        $issue->untrack(auth()->user());
        return redirect(route('issues'));
    }
}
