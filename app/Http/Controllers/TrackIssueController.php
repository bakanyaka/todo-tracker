<?php

namespace App\Http\Controllers;

use App\Exceptions\FailedToRetrieveRedmineDataException;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackIssueController extends Controller
{

    public function store(Request $request): JsonResponse
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


    public function destroy(Issue $issue): JsonResponse
    {
        $issue->untrack(auth()->user());
        return response()->json([]);
    }
}
