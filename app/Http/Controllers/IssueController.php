<?php

namespace App\Http\Controllers;

use App\BusinessDate;
use App\Facades\Redmine;
use App\Models\Issue;
use Carbon\Carbon;
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

        $issues =auth()->user()->issues;
        return view('issues.index', ['issues' => $issues]);
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
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function store()
    {
        $issue = Redmine::getIssue(request('issue_id'));

        if ($issue) {
            $issue = $issue['issue'];
        } else {
            // TODO: Return Issue not found response
            throw new Exception('Issue Not Found');
        }
        $createdOn = BusinessDate::parse($issue['created_on']);
        $dueDate = $createdOn->addBusinessHours(5);
        $issue = Issue::create([
            'id' => request('issue_id'),
            'subject' => $issue['subject'],
            'created_on' => $createdOn,
            'due_date' => $dueDate
        ]);
        $issue->trackedByUsers()->attach(auth()->user());
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
