<?php

namespace App\Http\Controllers\Api;

use App\Filters\IssueFilters;
use App\Http\Controllers\Controller;
use App\Http\Resources\IssueCollection;
use App\Jobs\SyncIssues;
use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class IssueController extends Controller
{

    public function index(IssueFilters $filters): IssueCollection
    {
        $issues = Issue::filter($filters)->with('users')->get();
        if (request()->has('overdue')) {
            $issues = $issues->filter(fn(Issue $issue) => $issue->getOverdueState()->is(request('overdue')));
        }
        $issues = $issues->sort([Issue::class, 'defaultSort'])->values();

        return new IssueCollection($issues);
    }

    public function destroy(Issue $issue): JsonResponse
    {
        $this->authorize('delete', $issue);
        $issue->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function sync(): JsonResponse
    {
        $forceUpdateAll = request()->input('force_update_all') ? true : false;
        if (request('updated_since')) {
            SyncIssues::dispatch(Carbon::parse(request('updated_since')), $forceUpdateAll);
        } else {
            SyncIssues::dispatch(null, $forceUpdateAll);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

}
