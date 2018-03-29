<?php

namespace App\Http\Controllers\Api;

use App\Facades\Redmine;
use App\Models\Assignee;
use App\Models\Synchronization;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AssigneeController extends Controller
{
    /**
     * Sync assignees with Redmine.
     *
     * @return \Illuminate\Http\Response
     */
    public function sync()
    {
        $rmAssignees = Redmine::getUsers();
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
