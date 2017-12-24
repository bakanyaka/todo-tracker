<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\IssueCollection;
use App\Models\Issue;
use function GuzzleHttp\Promise\all;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return IssueCollection
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
        switch (request('status')) {
            case 'closed':
                $issues->closed();
                break;
            case 'all':
                break;
            default:
                $issues->open();
        }
        $issues = $issues->get()->sort([Issue::class, 'defaultSort'])->values();//->paginate(5);
        return new IssueCollection($issues);
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
