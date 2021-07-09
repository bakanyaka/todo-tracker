<?php

namespace App\Http\Controllers;

use App\Models\Assignee;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IssuesGanttController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $issues = Issue::setEagerLoads([])->with('service')
            ->where(function (Builder $query) {
                $query->whereNotNull('service_id')->orWhereNotNull('due_date');
            })
            ->whereNotNull('assigned_to_id')
            ->when(
                $request->has('assignees'),
                fn(Builder $query) => $query->whereIn('assigned_to_id', $request->input('assignees'))
            )
            ->orderBy('subject')
            ->open()
            ->get()
            ->filter(fn(Issue $issue) => $issue->due_date !== null)
            ->groupBy('assigned_to_id');

        $assignees = Assignee::find($issues->keys());

        $ganttData = $issues->reduce(function (Collection $carry, Collection $value, $key) use ($assignees) {
            $assignee = $assignees->find($key);
            if (!$assignee) {
                return $carry;
            }
            return $carry->push([
                'id' => "a_{$assignee->id}",
                'text' => "$assignee->lastname $assignee->firstname",
                'color' => '#65c16f',
            ])->concat($value->map(fn(Issue $issue) => [
                'id' => $issue->id,
                'parent' => $issue->parent_id ?? "a_{$assignee->id}",
                'text' => "#{$issue->id}: {$issue->subject}",
                'start_date' => $issue->start_date->format('Y-m-d H:i:s'),
                'end_date' => $issue->due_date->format('Y-m-d H:i:s'),
            ]));
        }, collect());

        return response()->json(['data' => $ganttData->values()]);
    }
}
