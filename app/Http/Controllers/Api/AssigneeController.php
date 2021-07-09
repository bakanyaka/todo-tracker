<?php

namespace App\Http\Controllers\Api;

use App\Facades\RedmineApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssigneeResource;
use App\Models\Assignee;
use App\Models\Synchronization;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssigneeController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        $assignees = Assignee::orderBy('lastname')->orderBy('firstname')->get();
        return AssigneeResource::collection($assignees);
    }

    public function sync(): JsonResponse
    {
        $rmAssignees = RedmineApi::getUsers();
        foreach ($rmAssignees as $rmAssignee) {
            $assignee = Assignee::firstOrNew(['id' => $rmAssignee['id']]);
            $assignee->login = $rmAssignee['login'];
            $assignee->firstname = $rmAssignee['firstname'];
            $assignee->lastname = $rmAssignee['lastname'];
            $assignee->mail = $rmAssignee['mail'];
            $assignee->save();
        }
        Synchronization::create([
            'completed_at' => Carbon::now(),
            'type' => 'assignees',
        ]);
        return response()->json([], 200);
    }

}
