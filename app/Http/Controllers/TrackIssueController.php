<?php

namespace App\Http\Controllers;

use App\Exceptions\FailedToRetrieveRedmineDataException;
use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackIssueController extends Controller
{

    public function __construct(protected IssueService $issueService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['issue_id' => 'required|int']);
        $issue = Issue::firstOrNew(['id' => $request->issue_id]);
        try {
            $this->issueService->syncIssueWithRedmine($issue);
        } catch (FailedToRetrieveRedmineDataException $exception) {
            abort(404);
        }
        $issue->save();
        $issue->track(auth()->user());
        return response()->json([], 201);
    }


    public function destroy(Issue $issue): JsonResponse
    {
        $issue->untrack(auth()->user());
        return response()->json([]);
    }
}
