<template>
    <tr :class="{'table-danger': issue.time_left < 0}">
        <td>
            <a :href="`${redmineUri}/issues/${issue.id}`">{{issue.id}}</a>
        </td>
        <td>{{issue.subject}}</td>
        <td>{{issue.department}}</td>
        <td>{{issue.assigned_to}}</td>
        <td>{{issue.priority}}</td>
        <td>{{issue.service}}</td>
        <td>{{issue.estimated_hours}}</td>
        <td :class="{'table-warning': percentOfTimeLeft < 30}">{{issue.time_left}}</td>
        <td>{{issue.created_on}}</td>
        <td>{{issue.due_date}}</td>
        <td>{{issue.closed_on}}</td>
        <td class="align-middle">
            <a v-if="issue.is_tracked_by_current_user" class="btn btn-sm btn-primary" href="#" role="button">Удалить</a>
            <a v-else class="btn btn-sm btn-primary" href="#" role="button">Следить</a>
        </td>

    </tr>
<!--    <tr class="{{$issue->time_left < 0 ? 'table-danger' : ''}}">
        <td><a href="{{config('services.redmine.uri') . '/issues/' . $issue->id}}">{{$issue->id}}</a></td>
        <td>{{$issue->subject}}</td>
        <td>{{$issue->department}}</td>
        <td>{{$issue->assigned_to or ''}}</td>
        <td>{{$issue->priority->name}}</td>
        <td>{{$issue->service->name or 'Прочее'}}</td>
        <td>{{$issue->service->hours or ''}}</td>
        <td class="{{($issue->percent_of_time_left < 30 && $issue->percent_of_time_left !== null )? 'table-warning' : ''}}">
            {{$issue->time_left or ''}}
        </td>
        <td>{{$issue->created_on}}</td>
        <td>{{$issue->due_date or ''}}</td>
        <td>{{$issue->closed_on or ''}}</td>
        <td class="align-middle">
            @if($issue->isTrackedBy(auth()->user()))
            <a class="btn btn-sm btn-primary" href="#" onclick="event.preventDefault();document.getElementById('issue-{{$issue->id}}-untrack-form').submit();" role="button">Удалить</a>
            <form id="issue-{{$issue->id}}-untrack-form" action="{{route('issues.untrack', ['id' => $issue->id])}}" method="POST" style="display: none;">
                {{ csrf_field() }}
                {{method_field('DELETE')}}
            </form>
            @else
            <a class="btn btn-sm btn-primary" href="#" onclick="event.preventDefault();document.getElementById('issue-{{$issue->id}}-track-form').submit();" role="button">Следить</a>
            <form id="issue-{{$issue->id}}-track-form" action="{{route('issues.track', ['id' => $issue->id])}}" method="POST" style="display: none;">
                <input class="form-control form-control-sm mr-sm-2" name="issue_id" type="text" value="{{$issue->id}}" placeholder="# Задачи">
                {{ csrf_field() }}
            </form>
            @endif
        </td>
    </tr>-->
</template>

<script>
    export default {
        name: "issue",
        props: ['issue'],
        data () {
            return {
                redmineUri: config.redmineUri
            }
        },
        computed: {
            percentOfTimeLeft () {
                return this.issue.estimated_hours ? (this.issue.time_left / this.issue.estimated_hours * 100) : 100
            }
        }

    }
</script>

<style scoped>

</style>