<?php


namespace App\Services;


use App\Enums\OverdueState;
use App\Models\Assignee;
use App\Models\Issue;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class IssueGanttService
{
    public function getGanttData(array $filters)
    {
        $groupBy = Arr::get($filters, 'group_by', 'assigned_to');

        $issues = Issue::setEagerLoads([])->with('service', 'status')
            ->where(function (Builder $query) {
                $query->whereNotNull('service_id')->orWhereNotNull('due_date');
            })
            ->whereNotNull('assigned_to_id')
            ->when(
                array_key_exists('assignees', $filters),
                fn(Builder $query) => $query->whereIn('assigned_to_id', $filters['assignees'])
            )
            ->orderBy('subject')
            ->open()
            ->get()
            ->filter(fn(Issue $issue) => $issue->due_date !== null)
            ->groupBy("{$groupBy}_id");

        $groups = $groupBy === 'project' ? Project::find($issues->keys()) : Assignee::find($issues->keys());

        $ganttData = $issues->reduce(function (Collection $carry, Collection $value, $key) use ($groups) {
            $group = $groups->find($key);
            if (!$group) {
                return $carry;
            }
            return $carry->push([
                'id' => "g_{$group->id}",
                'text' => $group->name,
                'color' => '#65c16f',
            ])->concat($value->map(fn(Issue $issue) => [
                'id' => $issue->id,
                'parent' => $issue->parent_id ?? "g_{$group->id}",
                'text' => "#{$issue->id}: {$issue->subject}",
                'color' => $issue->getOverdueState()->is(OverdueState::Yes) ? '#ff4916' : '#3DB9D3',
                'start_date' => $issue->start_date->format('Y-m-d H:i:s'),
                'end_date' => $issue->due_date->format('Y-m-d H:i:s'),
                'progress' => round($issue->done_ratio / 100, 2),
            ]));
        }, collect());

        return $ganttData->values();
    }
}
