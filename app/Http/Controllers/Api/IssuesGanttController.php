<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IssueGanttService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssuesGanttController extends Controller
{

    public function __construct(protected IssueGanttService $issueGanttService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->issueGanttService->getGanttData($request->input())]);
    }
}
