<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FailedToRetrieveRedmineDataException;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Services\IssueSynchronizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackIssueController extends Controller
{

    public function __construct(protected IssueSynchronizationService $issueService)
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
