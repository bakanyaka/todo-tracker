<?php

namespace App\Http\Controllers;

use App\BusinessDate;
use App\Models\Issue;
use App\Services\RedmineService;
use Carbon\Carbon;
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
        $issues = Issue::all();
        return view('issues.index', ['issues' => $issues]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param RedmineService $redmine
     * @return \Illuminate\Http\Response
     */
    public function create(RedmineService $redmine)
    {
        $issue = $redmine->getIssue(request('issue_id'));
        return view('issues.create', ['issue' => $issue['issue']]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $dueDate = BusinessDate::parse(request('created_on'))->addBusinessHours(request('estimated_hours'));
        Issue::create([
            'issue_id' => request('issue_id'),
            'subject' => request('subject'),
            'created_on' => Carbon::parse(request('created_on')),
            'due_date' => $dueDate
        ]);
        return redirect(route('issues'));
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
        //
    }
}
